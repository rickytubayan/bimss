<?php
namespace Controllers\Admin;

class LivelihoodController extends \Controller {

    private const NAME_SQL = "CONCAT(r.last_name, ', ', IFNULL(CONCAT(r.first_name, ' ', IFNULL(r.middle_name, '')), r.first_name))";

    public function index() {
        $db = $this->db;

        $stats = [
            'jobs' => (int)$db->query("SELECT COUNT(*) c FROM job_postings WHERE deleted_at IS NULL")->fetch()['c'],
            'active_jobs' => (int)$db->query("SELECT COUNT(*) c FROM job_postings WHERE deleted_at IS NULL AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())")->fetch()['c'],
            'applications' => (int)$db->query("SELECT COUNT(*) c FROM job_applications WHERE deleted_at IS NULL")->fetch()['c'],
            'farmers' => (int)$db->query("SELECT COUNT(*) c FROM farmer_registry WHERE deleted_at IS NULL AND status = 'active'")->fetch()['c'],
        ];

        $recentJobs = $db->query("
            SELECT j.*,
                   (SELECT COUNT(*) FROM job_applications a WHERE a.job_id = j.id AND a.deleted_at IS NULL) AS application_count
            FROM job_postings j
            WHERE j.deleted_at IS NULL
            ORDER BY j.posted_at DESC, j.id DESC LIMIT 5
        ")->fetchAll();

        $this->viewAdmin('livelihood/index', [
            'title' => 'Livelihood',
            'stats' => $stats,
            'recentJobs' => $recentJobs,
        ]);
    }

    public function jobs() {
        $db = $this->db;

        $filter = $_GET['filter'] ?? '';
        $filter = in_array($filter, ['active', 'expired']) ? $filter : '';

        $where = "j.deleted_at IS NULL";
        if ($filter === 'active') {
            $where .= " AND j.is_active = 1 AND (j.expires_at IS NULL OR j.expires_at > NOW())";
        } elseif ($filter === 'expired') {
            $where .= " AND (j.is_active = 0 OR (j.expires_at IS NOT NULL AND j.expires_at <= NOW()))";
        }

        $jobs = $db->prepare("
            SELECT j.*,
                   (SELECT COUNT(*) FROM job_applications a WHERE a.job_id = j.id AND a.deleted_at IS NULL) AS application_count
            FROM job_postings j
            WHERE {$where}
            ORDER BY j.posted_at DESC, j.id DESC
        ");
        $jobs->execute();
        $rows = $jobs->fetchAll();

        $this->viewAdmin('livelihood/jobs', [
            'title' => 'Livelihood - Job Postings',
            'rows' => $rows,
            'filter' => $filter,
        ]);
    }

    public function storeJob() {
        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty(trim($input['company_name'] ?? ''))) $errors[] = 'Company name is required.';
        if (empty(trim($input['position'] ?? ''))) $errors[] = 'Position is required.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('livelihood/jobs'));
        }

        $stmt = $db->prepare("
            INSERT INTO job_postings (company_name, position, salary_range, description, requirements, expires_at, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($input['company_name']),
            trim($input['position']),
            trim($input['salary_range'] ?? ''),
            trim($input['description'] ?? ''),
            trim($input['requirements'] ?? ''),
            trim($input['expires_at'] ?? '') === '' ? null : trim($input['expires_at']) . ' 00:00:00',
            !empty($input['is_active']) ? 1 : 0,
        ]);

        flash('success', 'Job posting added.');
        redirect(admin_url('livelihood/jobs'));
    }

    public function farmers() {
        $db = $this->db;

        $status = $_GET['status'] ?? '';
        $status = in_array($status, ['active', 'inactive']) ? $status : '';

        $where = "f.deleted_at IS NULL";
        $params = [];
        if ($status !== '') {
            $where .= " AND f.status = ?";
            $params[] = $status;
        }

        $stmt = $db->prepare("
            SELECT f.*, " . self::NAME_SQL . " AS farmer_name
            FROM farmer_registry f
            JOIN residents r ON r.id = f.resident_id
            WHERE {$where}
            ORDER BY f.registration_date DESC, f.id DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $stats = [
            'total' => (int)$db->query("SELECT COUNT(*) c FROM farmer_registry WHERE deleted_at IS NULL")->fetch()['c'],
            'active' => (int)$db->query("SELECT COUNT(*) c FROM farmer_registry WHERE deleted_at IS NULL AND status = 'active'")->fetch()['c'],
            'hectares' => (float)$db->query("SELECT COALESCE(SUM(farm_size_hectares), 0) c FROM farmer_registry WHERE deleted_at IS NULL AND status = 'active'")->fetch()['c'],
        ];

        $residents = $db->query("
            SELECT r.id, " . self::NAME_SQL . " AS name
            FROM residents r
            WHERE r.deleted_at IS NULL AND r.status = 'active'
            ORDER BY r.last_name, r.first_name
        ")->fetchAll();

        $registered = $db->query("SELECT resident_id FROM farmer_registry WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_COLUMN);

        $this->viewAdmin('livelihood/farmers', [
            'title' => 'Livelihood - Farmer Registry',
            'rows' => $rows,
            'stats' => $stats,
            'status' => $status,
            'residents' => $residents,
            'registered' => $registered,
        ]);
    }

    public function storeFarmer() {
        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty($input['resident_id'] ?? '')) $errors[] = 'Farmer resident is required.';
        if (empty(trim($input['registration_date'] ?? ''))) $errors[] = 'Registration date is required.';
        if (($input['farm_size_hectares'] ?? '') !== '' && (float)$input['farm_size_hectares'] < 0) $errors[] = 'Farm size cannot be negative.';
        if (!in_array($input['status'] ?? '', ['active', 'inactive'])) $errors[] = 'Please select a valid status.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('livelihood/farmers'));
        }

        $exists = $db->prepare("SELECT id FROM farmer_registry WHERE resident_id = ? AND deleted_at IS NULL LIMIT 1");
        $exists->execute([(int)$input['resident_id']]);
        if ($exists->fetch()) {
            flash('error', 'This resident is already registered as a farmer.');
            redirect(admin_url('livelihood/farmers'));
        }

        $stmt = $db->prepare("
            INSERT INTO farmer_registry (resident_id, farm_size_hectares, primary_crops, livestock, registration_date, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$input['resident_id'],
            ($input['farm_size_hectares'] ?? '') === '' ? 0 : (float)$input['farm_size_hectares'],
            trim($input['primary_crops'] ?? ''),
            trim($input['livestock'] ?? ''),
            trim($input['registration_date']),
            $input['status'],
        ]);

        flash('success', 'Farmer registered.');
        redirect(admin_url('livelihood/farmers'));
    }
}