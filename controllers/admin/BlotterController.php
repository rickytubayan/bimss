<?php
namespace Controllers\Admin;

class BlotterController extends \Controller {

    public function index() {
        $db = $this->db;

        $status = $_GET['status'] ?? '';
        $status = in_array($status, ['filed', 'investigating', 'for_hearing', 'resolved', 'closed']) ? $status : '';

        $where = "b.deleted_at IS NULL";
        $params = [];
        if ($status !== '') {
            $where .= " AND b.status = ?";
            $params[] = $status;
        }

        $stmt = $db->prepare("
            SELECT b.*, p.name AS purok_name
            FROM blotters b
            LEFT JOIN puroks p ON p.id = b.purok_id
            WHERE {$where}
            ORDER BY b.date_time_of_incident DESC, b.id DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $stats = [
            'total' => (int)$db->query("SELECT COUNT(*) c FROM blotters WHERE deleted_at IS NULL")->fetch()['c'],
            'open' => (int)$db->query("SELECT COUNT(*) c FROM blotters WHERE deleted_at IS NULL AND status IN ('filed','investigating','for_hearing')")->fetch()['c'],
            'resolved' => (int)$db->query("SELECT COUNT(*) c FROM blotters WHERE deleted_at IS NULL AND status = 'resolved'")->fetch()['c'],
            'closed' => (int)$db->query("SELECT COUNT(*) c FROM blotters WHERE deleted_at IS NULL AND status = 'closed'")->fetch()['c'],
        ];

        $this->viewAdmin('blotter/index', [
            'title' => 'Blotter',
            'rows' => $rows,
            'stats' => $stats,
            'status' => $status,
            'puroks' => $db->query("SELECT id, name FROM puroks WHERE deleted_at IS NULL ORDER BY name")->fetchAll(),
        ]);
    }

    public function store() {
        $input = $this->getInput();

        $errors = [];
        if (empty(trim($input['reporter_name'] ?? ''))) $errors[] = 'Reporter name is required.';
        if (!in_array($input['incident_type'] ?? '', ['theft', 'physical_injury', 'vawc', 'fraud', 'quarrel', 'trespassing', 'other'])) $errors[] = 'Please select a valid incident type.';
        if (empty(trim($input['narrative'] ?? ''))) $errors[] = 'Incident narrative is required.';
        if (empty(trim($input['date_time_of_incident'] ?? ''))) $errors[] = 'Date/time of incident is required.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('blotter'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO blotters
                (report_type, reporter_name, reporter_contact, incident_type, narrative, location, purok_id, date_time_of_incident, case_number)
            VALUES ('walk_in', ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($input['reporter_name']),
            ($input['reporter_contact'] ?? '') === '' ? null : trim($input['reporter_contact']),
            $input['incident_type'],
            trim($input['narrative']),
            ($input['location'] ?? '') === '' ? null : trim($input['location']),
            ($input['purok_id'] ?? '') === '' ? null : (int)$input['purok_id'],
            $input['date_time_of_incident'],
            generate_tracking_code('BLT'),
        ]);

        flash('success', 'Blotter entry registered.');
        redirect(admin_url('blotter'));
    }

    public function show($id) {
        $db = $this->db;

        $stmt = $db->prepare("
            SELECT b.*, p.name AS purok_name,
                   CONCAT(u.first_name, ' ', u.last_name) AS tanod_name
            FROM blotters b
            LEFT JOIN puroks p ON p.id = b.purok_id
            LEFT JOIN users u ON u.id = b.tanod_assigned
            WHERE b.id = ? AND b.deleted_at IS NULL
        ");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        if (!$row) {
            flash('error', 'Blotter entry not found.');
            redirect(admin_url('blotter'));
        }

        $witnessStmt = $db->prepare("SELECT * FROM blotter_witnesses WHERE blotter_id = ? AND deleted_at IS NULL ORDER BY id");
        $witnessStmt->execute([(int)$id]);
        $witnesses = $witnessStmt->fetchAll();

        $this->viewAdmin('blotter/show', [
            'title' => 'Blotter #' . $row['id'],
            'row' => $row,
            'witnesses' => $witnesses,
            'users' => $db->query("SELECT id, CONCAT(first_name, ' ', last_name) AS label FROM users WHERE status = 'active' ORDER BY first_name")->fetchAll(),
            'statuses' => ['filed', 'investigating', 'for_hearing', 'resolved', 'closed'],
        ]);
    }

    public function update($id) {
        $input = $this->getInput();

        $errors = [];
        if (!in_array($input['status'] ?? '', ['filed', 'investigating', 'for_hearing', 'resolved', 'closed'])) $errors[] = 'Please select a valid status.';

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(admin_url('blotter/' . (int)$id));
        }

        $stmt = $this->db->prepare("
            UPDATE blotters
            SET status = ?, tanod_assigned = ?, case_number = ?
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([
            $input['status'],
            ($input['tanod_assigned'] ?? '') === '' ? null : (int)$input['tanod_assigned'],
            ($input['case_number'] ?? '') === '' ? null : trim($input['case_number']),
            (int)$id,
        ]);

        flash('success', 'Blotter entry updated.');
        redirect(admin_url('blotter/' . (int)$id));
    }
}