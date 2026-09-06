<?php
namespace Controllers\Admin;

class EvacuationController extends \Controller {

    private const STATUSES = ['open', 'closed', 'full', 'maintenance'];
    private const OCCUPANT_STATUSES = ['evacuated', 'returned', 'transferred'];

    private const NAME_SQL = "CONCAT(r.last_name, ', ', IFNULL(CONCAT(r.first_name, ' ', IFNULL(r.middle_name, '')), r.first_name))";

    private const FACILITIES = ['water', 'toilet', 'sleeping_area', 'kitchen', 'medical', 'generator', 'wifi', 'prayer_area'];

    public function index() {
        $db = $this->db;

        $status = $_GET['status'] ?? '';
        $status = in_array($status, self::STATUSES) ? $status : '';

        $where = "c.deleted_at IS NULL";
        $params = [];
        if ($status !== '') {
            $where .= " AND c.status = ?";
            $params[] = $status;
        }

        $stmt = $db->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM evacuation_occupants o WHERE o.center_id = c.id AND o.status = 'evacuated' AND o.deleted_at IS NULL) AS occupant_count
            FROM evacuation_centers c
            WHERE {$where}
            ORDER BY c.name
        ");
        $stmt->execute($params);
        $centers = $stmt->fetchAll();

        $stats = [
            'total' => (int)$db->query("SELECT COUNT(*) c FROM evacuation_centers WHERE deleted_at IS NULL")->fetch()['c'],
            'open' => (int)$db->query("SELECT COUNT(*) c FROM evacuation_centers WHERE deleted_at IS NULL AND status = 'open'")->fetch()['c'],
            'full' => (int)$db->query("SELECT COUNT(*) c FROM evacuation_centers WHERE deleted_at IS NULL AND status = 'full'")->fetch()['c'],
            'evacuated' => (int)$db->query("SELECT COUNT(*) c FROM evacuation_occupants WHERE deleted_at IS NULL AND status = 'evacuated'")->fetch()['c'],
        ];

        $residents = $db->query("
            SELECT r.id, " . self::NAME_SQL . " AS name
            FROM residents r
            WHERE r.deleted_at IS NULL AND r.status = 'active'
            ORDER BY r.last_name, r.first_name
        ")->fetchAll();

        $this->viewAdmin('evacuation/index', [
            'title' => 'Evacuation Centers',
            'centers' => $centers,
            'stats' => $stats,
            'status' => $status,
            'residents' => $residents,
        ]);
    }

    public function store() {
        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty(trim($input['name'] ?? ''))) $errors[] = 'Center name is required.';
        if (($input['max_capacity'] ?? '') === '' || (int)$input['max_capacity'] < 0) $errors[] = 'Max capacity must be zero or a positive number.';
        if (!in_array($input['status'] ?? '', self::STATUSES)) $errors[] = 'Please select a valid status.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('evacuation'));
        }

        $facilityList = [];
        foreach (self::FACILITIES as $f) {
            if (!empty($input['facilities'][$f])) {
                $facilityList[] = $f;
            }
        }

        $config = require __DIR__ . '/../../config/app.php';
        $barangayName = $config['barangay']['name'] ?? '';
        $barangayId = null;
        if ($barangayName) {
            $stmt = $db->prepare("SELECT id FROM barangays WHERE name = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$barangayName]);
            $row = $stmt->fetch();
            if ($row) $barangayId = (int)$row['id'];
        }
        if (!$barangayId) {
            $stmt = $db->query("SELECT id FROM barangays WHERE deleted_at IS NULL LIMIT 1");
            $row = $stmt->fetch();
            if ($row) $barangayId = (int)$row['id'];
        }
        if (!$barangayId) {
            set_old_input($input);
            flash('error', 'No barangay found. Please seed geographic reference data first.');
            redirect(admin_url('evacuation'));
        }

        $stmt = $db->prepare("
            INSERT INTO evacuation_centers (name, address, barangay_id, max_capacity, current_occupancy, facilities_json, status)
            VALUES (?, ?, ?, 0, 0, ?, ?)
        ");
        $stmt->execute([
            trim($input['name']),
            trim($input['address'] ?? ''),
            $barangayId,
            !empty($facilityList) ? json_encode($facilityList) : null,
            $input['status'],
        ]);

        flash('success', 'Evacuation center registered.');
        redirect(admin_url('evacuation'));
    }

    public function show($id) {
        $db = $this->db;
        $id = (int)$id;

        $stmt = $db->prepare("SELECT * FROM evacuation_centers WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $center = $stmt->fetch();

        if (!$center) {
            flash('error', 'Evacuation center not found.');
            redirect(admin_url('evacuation'));
        }

        $occupants = $db->prepare("
            SELECT o.*, " . self::NAME_SQL . " AS resident_name
            FROM evacuation_occupants o
            JOIN residents r ON r.id = o.resident_id
            WHERE o.center_id = ? AND o.deleted_at IS NULL
            ORDER BY o.date_in DESC, o.id DESC
        ");
        $occupants->execute([$id]);
        $occupantList = $occupants->fetchAll();

        $stats = [
            'current' => (int)$db->query("SELECT COUNT(*) c FROM evacuation_occupants WHERE center_id = {$id} AND deleted_at IS NULL AND status = 'evacuated'")->fetch()['c'],
            'returned' => (int)$db->query("SELECT COUNT(*) c FROM evacuation_occupants WHERE center_id = {$id} AND deleted_at IS NULL AND status = 'returned'")->fetch()['c'],
            'transferred' => (int)$db->query("SELECT COUNT(*) c FROM evacuation_occupants WHERE center_id = {$id} AND deleted_at IS NULL AND status = 'transferred'")->fetch()['c'],
            'total_ever' => (int)$db->query("SELECT COUNT(*) c FROM evacuation_occupants WHERE center_id = {$id} AND deleted_at IS NULL")->fetch()['c'],
        ];

        $residents = $db->query("
            SELECT r.id, " . self::NAME_SQL . " AS name
            FROM residents r
            WHERE r.deleted_at IS NULL AND r.status = 'active'
            ORDER BY r.last_name, r.first_name
        ")->fetchAll();

        $facilities = json_decode($center['facilities_json'] ?? '[]', true) ?? [];

        $this->viewAdmin('evacuation/show', [
            'title' => 'Evacuation Center - ' . $center['name'],
            'center' => $center,
            'occupants' => $occupantList,
            'stats' => $stats,
            'residents' => $residents,
            'facilities' => $facilities,
        ]);
    }

    public function checkIn($id) {
        $input = $this->getInput();
        $db = $this->db;
        $id = (int)$id;

        $stmt = $db->prepare("SELECT * FROM evacuation_centers WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $center = $stmt->fetch();

        if (!$center) {
            flash('error', 'Evacuation center not found.');
            redirect(admin_url('evacuation'));
        }

        $residentId = (int)($input['resident_id'] ?? 0);
        if (!$residentId) {
            flash('error', 'Please select a resident.');
            redirect(admin_url('evacuation/' . $id));
        }

        $existing = $db->prepare("SELECT id FROM evacuation_occupants WHERE center_id = ? AND resident_id = ? AND status = 'evacuated' AND deleted_at IS NULL LIMIT 1");
        $existing->execute([$id, $residentId]);
        if ($existing->fetch()) {
            flash('error', 'This resident is already checked in at this center.');
            redirect(admin_url('evacuation/' . $id));
        }

        $db->beginTransaction();
        try {
            $ins = $db->prepare("INSERT INTO evacuation_occupants (center_id, resident_id, date_in, status) VALUES (?, ?, CURDATE(), 'evacuated')");
            $ins->execute([$id, $residentId]);

            $db->prepare("UPDATE evacuation_centers SET current_occupancy = current_occupancy + 1 WHERE id = ? AND deleted_at IS NULL")->execute([$id]);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not check in resident. Please try again.');
            redirect(admin_url('evacuation/' . $id));
        }

        flash('success', 'Resident checked in.');
        redirect(admin_url('evacuation/' . $id));
    }

    public function checkOut($id) {
        $input = $this->getInput();
        $db = $this->db;
        $id = (int)$id;

        $stmt = $db->prepare("SELECT * FROM evacuation_centers WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $center = $stmt->fetch();

        if (!$center) {
            flash('error', 'Evacuation center not found.');
            redirect(admin_url('evacuation'));
        }

        $occupantId = (int)($input['occupant_id'] ?? 0);
        if (!$occupantId) {
            flash('error', 'Invalid occupant.');
            redirect(admin_url('evacuation/' . $id));
        }

        $occ = $db->prepare("SELECT * FROM evacuation_occupants WHERE id = ? AND center_id = ? AND deleted_at IS NULL");
        $occ->execute([$occupantId, $id]);
        $occupant = $occ->fetch();

        if (!$occupant || $occupant['status'] !== 'evacuated') {
            flash('error', 'Occupant not found or already checked out.');
            redirect(admin_url('evacuation/' . $id));
        }

        $outcome = in_array($input['outcome'] ?? '', self::OCCUPANT_STATUSES) ? $input['outcome'] : 'returned';

        $db->beginTransaction();
        try {
            $db->prepare("UPDATE evacuation_occupants SET date_out = CURDATE(), status = ? WHERE id = ? AND deleted_at IS NULL")->execute([$outcome, $occupantId]);

            $db->prepare("UPDATE evacuation_centers SET current_occupancy = GREATEST(current_occupancy - 1, 0) WHERE id = ? AND deleted_at IS NULL")->execute([$id]);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not check out resident. Please try again.');
            redirect(admin_url('evacuation/' . $id));
        }

        flash('success', 'Resident checked out.');
        redirect(admin_url('evacuation/' . $id));
    }
}
