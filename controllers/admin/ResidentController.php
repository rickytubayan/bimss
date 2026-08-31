<?php
namespace Controllers\Admin;

use Models\Resident;
use Models\Household;

class ResidentController extends \Controller {

    public function index() {
        $resident = new Resident();
        $db = $this->db;

        $search = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $where = "WHERE r.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $where .= " AND (r.first_name LIKE ? OR r.last_name LIKE ? OR r.middle_name LIKE ? OR r.national_id LIKE ?)";
            $like = "%{$search}%";
            array_push($params, $like, $like, $like, $like);
        }
        if ($status !== '') {
            $where .= " AND r.status = ?";
            $params[] = $status;
        }

        $perPage = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM residents r {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $db->prepare("
            SELECT r.*, p.name AS purok_name, h.house_number, h.street
            FROM residents r
            LEFT JOIN puroks p ON p.id = r.purok_id
            LEFT JOIN households h ON h.id = r.household_id
            {$where}
            ORDER BY r.last_name, r.first_name
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $residents = $stmt->fetchAll();

        $totalPages = (int)ceil($total / $perPage);

        $this->viewAdmin('residents/index', [
            'title' => 'Residents',
            'residents' => $residents,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create() {
        $db = $this->db;
        $puroks = $db->query("SELECT id, name FROM puroks WHERE deleted_at IS NULL ORDER BY name")->fetchAll();
        $households = $db->query("SELECT id, house_number, street FROM households WHERE deleted_at IS NULL ORDER BY street, house_number")->fetchAll();

        $this->viewAdmin('residents/create', [
            'title' => 'Add Resident',
            'puroks' => $puroks,
            'households' => $households,
        ]);
    }

    public function store() {
        $input = $this->getInput();

        $errors = $this->validateResident($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('residents/create'));
        }

        $data = $this->extractResidentData($input);

        $data['birthdate'] = $this->normalizeBirthdate($data['birthdate']);
        $data['monthly_income'] = ($data['monthly_income'] !== '' && $data['monthly_income'] !== null) ? $data['monthly_income'] : null;

        $resident = new Resident();
        $id = $resident->create($data);

        flash('success', 'Resident added successfully.');
        redirect(admin_url('residents/' . $id));
    }

    public function show($id) {
        $resident = new Resident();
        $residentData = $resident->find($id);
        if (!$residentData) {
            flash('error', 'Resident not found.');
            redirect(admin_url('residents'));
        }

        $household = null;
        if ($residentData['household_id']) {
            $household = (new Household())->find($residentData['household_id']);
        }
        $purok = null;
        if ($residentData['purok_id']) {
            $stmt = $this->db->prepare("SELECT * FROM puroks WHERE id = ?");
            $stmt->execute([$residentData['purok_id']]);
            $purok = $stmt->fetch();
        }

        $this->viewAdmin('residents/show', [
            'title' => $resident->getFullName($residentData),
            'resident' => $residentData,
            'household' => $household,
            'purok' => $purok,
        ]);
    }

    public function edit($id) {
        $resident = new Resident();
        $residentData = $resident->find($id);
        if (!$residentData) {
            flash('error', 'Resident not found.');
            redirect(admin_url('residents'));
        }

        $db = $this->db;
        $puroks = $db->query("SELECT id, name FROM puroks WHERE deleted_at IS NULL ORDER BY name")->fetchAll();
        $households = $db->query("SELECT id, house_number, street FROM households WHERE deleted_at IS NULL ORDER BY street, house_number")->fetchAll();

        $this->viewAdmin('residents/edit', [
            'title' => 'Edit Resident',
            'resident' => $residentData,
            'puroks' => $puroks,
            'households' => $households,
        ]);
    }

    public function update($id) {
        $resident = new Resident();
        if (!$resident->find($id)) {
            flash('error', 'Resident not found.');
            redirect(admin_url('residents'));
        }

        $input = $this->getInput();

        $errors = $this->validateResident($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('residents/' . $id . '/edit'));
        }

        $data = $this->extractResidentData($input);
        $data['birthdate'] = $this->normalizeBirthdate($data['birthdate']);
        $data['monthly_income'] = ($data['monthly_income'] !== '' && $data['monthly_income'] !== null) ? $data['monthly_income'] : null;

        $resident->update($id, $data);

        flash('success', 'Resident updated successfully.');
        redirect(admin_url('residents/' . $id));
    }

    public function delete($id) {
        $resident = new Resident();
        if (!$resident->find($id)) {
            flash('error', 'Resident not found.');
            redirect(admin_url('residents'));
        }
        $resident->delete($id);
        flash('success', 'Resident deleted successfully.');
        redirect(admin_url('residents'));
    }

    private function validateResident(array $input) {
        $errors = [];
        if (empty(trim($input['first_name'] ?? ''))) $errors[] = 'First name is required.';
        if (empty(trim($input['last_name'] ?? ''))) $errors[] = 'Last name is required.';
        if (empty(trim($input['sex'] ?? ''))) $errors[] = 'Sex is required.';
        if (empty(trim($input['birthdate'] ?? ''))) $errors[] = 'Birthdate is required.';
        return $errors;
    }

    private function extractResidentData(array $input) {
        $data = [];
        foreach ($this->residentFields() as $field) {
            if (array_key_exists($field, $input)) {
                $value = is_array($input[$field]) ? trim(implode(',', $input[$field])) : trim((string)$input[$field]);
                $data[$field] = ($value === '') ? null : $value;
            }
        }
        foreach (['is_pwd', 'is_senior', 'is_voter', 'is_approved'] as $flag) {
            $data[$flag] = isset($input[$flag]) ? 1 : 0;
        }
        $data['status'] = $input['status'] ?? 'active';
        return $data;
    }

    private function residentFields() {
        return [
            'household_id', 'purok_id', 'national_id', 'first_name', 'middle_name',
            'last_name', 'suffix', 'sex', 'birthdate', 'civil_status', 'blood_type',
            'disability_type', 'educational_attainment', 'occupation', 'monthly_income',
            'phone', 'email', 'photo',
        ];
    }

    private function normalizeBirthdate($date) {
        $date = trim((string)$date);
        if ($date === '') return null;
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
