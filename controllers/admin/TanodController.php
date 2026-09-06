<?php
namespace Controllers\Admin;

class TanodController extends \Controller {

    public function index() {
        $db = $this->db;

        $roster = $db->query("
            SELECT id, username, first_name, last_name, status
            FROM users
            WHERE role = 'tanod' AND deleted_at IS NULL
            ORDER BY last_name, first_name
        ")->fetchAll();

        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));

        $stats = [
            'roster' => (int)$db->query("SELECT COUNT(*) c FROM users WHERE role = 'tanod' AND deleted_at IS NULL AND status = 'active'")->fetch()['c'],
            'schedules' => (int)$db->query("SELECT COUNT(*) c FROM tanod_schedules WHERE deleted_at IS NULL")->fetch()['c'],
            'this_week' => (int)$db->query("SELECT COUNT(*) c FROM tanod_schedules WHERE deleted_at IS NULL AND status = 'active' AND schedule_date >= '{$weekStart}'")->fetch()['c'],
            'today' => (int)$db->query("SELECT COUNT(*) c FROM tanod_schedules WHERE deleted_at IS NULL AND status = 'active' AND schedule_date = '{$today}'")->fetch()['c'],
        ];

        $upcoming = $db->query("
            SELECT * FROM tanod_schedules
            WHERE deleted_at IS NULL AND schedule_date >= '{$today}'
            ORDER BY schedule_date, shift_start
            LIMIT 5
        ")->fetchAll();

        $this->viewAdmin('tanod/index', [
            'title' => 'Tanod & CCTV',
            'roster' => $roster,
            'stats' => $stats,
            'upcoming' => $upcoming,
        ]);
    }

    public function schedule() {
        $db = $this->db;

        $status = $_GET['status'] ?? '';
        $status = in_array($status, ['active', 'completed', 'cancelled']) ? $status : '';

        $where = "s.deleted_at IS NULL";
        $params = [];
        if ($status !== '') {
            $where .= " AND s.status = ?";
            $params[] = $status;
        }

        $stmt = $db->prepare("
            SELECT s.*
            FROM tanod_schedules s
            WHERE {$where}
            ORDER BY s.schedule_date DESC, s.shift_start DESC, s.id DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $tanods = $db->query("
            SELECT id, CONCAT(first_name, ' ', last_name) AS label
            FROM users
            WHERE role = 'tanod' AND deleted_at IS NULL AND status = 'active'
            ORDER BY last_name, first_name
        ")->fetchAll();

        $this->viewAdmin('tanod/schedule', [
            'title' => 'Tanod - Schedules',
            'rows' => $rows,
            'status' => $status,
            'tanods' => $tanods,
        ]);
    }

    public function storeSchedule() {
        $input = $this->getInput();

        $errors = [];
        if (empty($input['schedule_date'] ?? '')) $errors[] = 'Schedule date is required.';
        if (empty($input['shift_start'] ?? '')) $errors[] = 'Shift start is required.';
        if (empty($input['shift_end'] ?? '')) $errors[] = 'Shift end is required.';
        if (!in_array($input['assignment_type'] ?? '', ['patrol', 'checkpoint', 'standby', 'event_duty'])) $errors[] = 'Please select a valid assignment type.';
        if (empty($input['assigned_members'] ?? [])) $errors[] = 'Assign at least one tanod member.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('tanod/schedule'));
        }

        $assigned = array_filter(array_map('trim', (array)($input['assigned_members'] ?? [])));

        $stmt = $this->db->prepare("
            INSERT INTO tanod_schedules (schedule_date, shift_start, shift_end, assignment_type, assigned_members, status)
            VALUES (?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([
            $input['schedule_date'],
            $input['shift_start'],
            $input['shift_end'],
            $input['assignment_type'],
            json_encode(array_values($assigned)),
        ]);

        flash('success', 'Tanod schedule saved.');
        redirect(admin_url('tanod/schedule'));
    }

    public function cctv() {
        $db = $this->db;

        $cameras = $db->query("
            SELECT * FROM cctv_cameras
            WHERE deleted_at IS NULL
            ORDER BY name
        ")->fetchAll();

        $stats = [
            'total' => count($cameras),
            'online' => count(array_filter($cameras, fn($c) => $c['status'] === 'online')),
            'offline' => count(array_filter($cameras, fn($c) => $c['status'] === 'offline')),
        ];

        $this->viewAdmin('tanod/cctv', [
            'title' => 'Tanod - CCTV',
            'cameras' => $cameras,
            'stats' => $stats,
        ]);
    }
}