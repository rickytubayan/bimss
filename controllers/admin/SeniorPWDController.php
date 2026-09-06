<?php
namespace Controllers\Admin;

class SeniorPWDController extends \Controller {

    private const NAME_SQL = "CONCAT(r.last_name, ', ', IFNULL(CONCAT(r.first_name, ' ', IFNULL(r.middle_name, '')), r.first_name))";

    public function index() {
        $db = $this->db;

        $type = $_GET['type'] ?? '';
        $type = in_array($type, ['senior', 'pwd']) ? $type : '';

        $where = "s.deleted_at IS NULL";
        $params = [];
        if ($type !== '') {
            $where .= " AND s.type = ?";
            $params[] = $type;
        }

        $stmt = $db->prepare("
            SELECT s.*, " . self::NAME_SQL . " AS resident_name
            FROM senior_pwd_profiles s
            JOIN residents r ON r.id = s.resident_id
            WHERE {$where}
            ORDER BY s.created_at DESC, s.id DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $stats = [
            'seniors' => (int)$db->query("SELECT COUNT(*) c FROM senior_pwd_profiles WHERE deleted_at IS NULL AND type = 'senior'")->fetch()['c'],
            'pwd' => (int)$db->query("SELECT COUNT(*) c FROM senior_pwd_profiles WHERE deleted_at IS NULL AND type = 'pwd'")->fetch()['c'],
            'active_pensioners' => (int)$db->query("SELECT COUNT(*) c FROM senior_pwd_profiles WHERE deleted_at IS NULL AND type = 'senior' AND pension_status = 'active'")->fetch()['c'],
            'monthly_allowance' => (float)$db->query("SELECT COALESCE(SUM(monthly_allowance), 0) c FROM senior_pwd_profiles WHERE deleted_at IS NULL AND type = 'senior' AND pension_status = 'active'")->fetch()['c'],
        ];

        $this->viewAdmin('seniors/index', [
            'title' => 'Senior & PWD',
            'rows' => $rows,
            'stats' => $stats,
            'type' => $type,
            'residents' => $this->residentOptions(),
        ]);
    }

    public function store() {
        $input = $this->getInput();

        $errors = [];
        if (empty($input['resident_id'] ?? '')) $errors[] = 'Resident is required.';
        if (!in_array($input['type'] ?? '', ['senior', 'pwd'])) $errors[] = 'Please select a valid type.';
        if (!in_array($input['pension_status'] ?? '', ['active', 'inactive', 'pending'])) $errors[] = 'Please select a valid pension status.';
        if (($input['monthly_allowance'] ?? '') !== '' && (float)$input['monthly_allowance'] < 0) $errors[] = 'Monthly allowance cannot be negative.';
        if (($input['grocery_benefits'] ?? '') !== '' && (float)$input['grocery_benefits'] < 0) $errors[] = 'Grocery benefits cannot be negative.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('seniors'));
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO senior_pwd_profiles
                    (resident_id, type, id_type, id_number, pension_status, monthly_allowance, grocery_benefits, osca_number, pwd_number)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                (int)$input['resident_id'],
                $input['type'],
                ($input['id_type'] ?? '') === '' ? null : trim($input['id_type']),
                ($input['id_number'] ?? '') === '' ? null : trim($input['id_number']),
                $input['pension_status'],
                (float)($input['monthly_allowance'] ?? 0),
                (float)($input['grocery_benefits'] ?? 0),
                ($input['osca_number'] ?? '') === '' ? null : trim($input['osca_number']),
                ($input['pwd_number'] ?? '') === '' ? null : trim($input['pwd_number']),
            ]);

            flash('success', ucfirst($input['type']) . ' profile saved successfully.');
            redirect(admin_url('seniors'));
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                set_old_input($input);
                flash('error', 'This resident already has a ' . $input['type'] . ' profile.');
            } else {
                set_old_input($input);
                flash('error', 'Could not save the profile. Please try again.');
            }
            redirect(admin_url('seniors'));
        }
    }

    private function residentOptions() {
        return $this->db->query("
            SELECT id, CONCAT(last_name, ', ', first_name) AS label
            FROM residents
            WHERE deleted_at IS NULL AND status = 'active'
            ORDER BY last_name, first_name
        ")->fetchAll();
    }
}