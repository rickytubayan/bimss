<?php
namespace Controllers\Admin;

use Models\Household;

class HouseholdController extends \Controller {

    public function index() {
        $db = $this->db;

        $search = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $classification = trim($_GET['classification'] ?? '');

        $where = "WHERE h.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $where .= " AND (h.house_number LIKE ? OR h.street LIKE ?)";
            $like = "%{$search}%";
            array_push($params, $like, $like);
        }
        if ($status !== '') {
            $where .= " AND h.status = ?";
            $params[] = $status;
        }
        if ($classification !== '') {
            $where .= " AND h.classification = ?";
            $params[] = $classification;
        }

        $perPage = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM households h {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $db->prepare("
            SELECT h.*, p.name AS purok_name,
                   (SELECT COUNT(*) FROM residents r WHERE r.household_id = h.id AND r.deleted_at IS NULL) AS member_count
            FROM households h
            LEFT JOIN puroks p ON p.id = h.purok_id
            {$where}
            ORDER BY h.street, h.house_number
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $households = $stmt->fetchAll();

        $totalPages = (int)ceil($total / $perPage);

        $this->viewAdmin('households/index', [
            'title' => 'Households',
            'households' => $households,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'status' => $status,
            'classification' => $classification,
        ]);
    }

    public function create() {
        $db = $this->db;
        $puroks = $db->query("SELECT id, name FROM puroks WHERE deleted_at IS NULL ORDER BY name")->fetchAll();

        $this->viewAdmin('households/create', [
            'title' => 'Add Household',
            'puroks' => $puroks,
        ]);
    }

    public function store() {
        $input = $this->getInput();

        $errors = $this->validateHousehold($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('households/create'));
        }

        $data = $this->extractData($input);

        $household = new Household();
        $id = $household->create($data);

        flash('success', 'Household added successfully.');
        redirect(admin_url('households/' . $id));
    }

    public function show($id) {
        $household = new Household();
        $data = $household->find($id);
        if (!$data) {
            flash('error', 'Household not found.');
            redirect(admin_url('households'));
        }

        $db = $this->db;

        $purok = null;
        if ($data['purok_id']) {
            $stmt = $db->prepare("SELECT * FROM puroks WHERE id = ?");
            $stmt->execute([$data['purok_id']]);
            $purok = $stmt->fetch();
        }

        $members = $household->getMembers($id);
        $head = $household->getHead($id);

        $stmt = $db->prepare("SELECT * FROM pets WHERE household_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $pets = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT * FROM vehicles WHERE household_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $vehicles = $stmt->fetchAll();

        $this->viewAdmin('households/show', [
            'title' => 'Household #' . $data['id'],
            'household' => $data,
            'purok' => $purok,
            'members' => $members,
            'head' => $head,
            'pets' => $pets,
            'vehicles' => $vehicles,
        ]);
    }

    public function edit($id) {
        $household = new Household();
        $data = $household->find($id);
        if (!$data) {
            flash('error', 'Household not found.');
            redirect(admin_url('households'));
        }

        $puroks = $this->db->query("SELECT id, name FROM puroks WHERE deleted_at IS NULL ORDER BY name")->fetchAll();

        $this->viewAdmin('households/edit', [
            'title' => 'Edit Household',
            'household' => $data,
            'puroks' => $puroks,
        ]);
    }

    public function update($id) {
        $household = new Household();
        if (!$household->find($id)) {
            flash('error', 'Household not found.');
            redirect(admin_url('households'));
        }

        $input = $this->getInput();

        $errors = $this->validateHousehold($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('households/' . $id . '/edit'));
        }

        $data = $this->extractData($input);
        $household->update($id, $data);

        flash('success', 'Household updated successfully.');
        redirect(admin_url('households/' . $id));
    }

    public function delete($id) {
        $household = new Household();
        if (!$household->find($id)) {
            flash('error', 'Household not found.');
            redirect(admin_url('households'));
        }
        $household->delete($id);
        flash('success', 'Household deleted successfully.');
        redirect(admin_url('households'));
    }

    private function validateHousehold(array $input) {
        $errors = [];
        if (empty(trim($input['street'] ?? '')) && empty(trim($input['house_number'] ?? ''))) {
            $errors[] = 'Either street or house number is required.';
        }
        return $errors;
    }

    private function extractData(array $input) {
        $fields = ['barangay_id', 'purok_id', 'house_number', 'street', 'classification', 'status'];
        $data = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $input)) {
                $value = trim((string)$input[$field]);
                $data[$field] = ($value === '') ? null : $value;
            }
        }
        if (!isset($data['barangay_id']) || $data['barangay_id'] === null) {
            if (!empty($data['purok_id'])) {
                $stmt = $this->db->prepare("SELECT barangay_id FROM puroks WHERE id = ? LIMIT 1");
                $stmt->execute([$data['purok_id']]);
                $row = $stmt->fetch();
                if ($row) $data['barangay_id'] = $row['barangay_id'];
            }
            if (empty($data['barangay_id'])) {
                $config = require __DIR__ . '/../../config/app.php';
                $barangayName = $config['barangay']['name'] ?? '';
                if ($barangayName) {
                    $stmt = $this->db->prepare("SELECT id FROM barangays WHERE name = ? LIMIT 1");
                    $stmt->execute([$barangayName]);
                    $row = $stmt->fetch();
                    if ($row) $data['barangay_id'] = $row['id'];
                }
            }
        }
        $data['classification'] = $data['classification'] ?? 'residential';
        $data['status'] = $data['status'] ?? 'active';
        return $data;
    }
}
