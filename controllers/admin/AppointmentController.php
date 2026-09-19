<?php
namespace Controllers\Admin;

class AppointmentController extends \Controller {

    public function index() {
        $db = $this->db;
        $status = trim($_GET['status'] ?? '');

        $where = "WHERE a.deleted_at IS NULL";
        $params = [];

        if ($status !== '' && in_array($status, ['booked','completed','cancelled','no_show'])) {
            $where .= " AND a.status = ?";
            $params[] = $status;
        }

        $perPage = 15;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM appointments a {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $db->prepare("
            SELECT a.*, r.first_name, r.last_name, r.email, r.phone,
                   s.slot_date, s.time_start, s.time_end, s.slot_type
            FROM appointments a
            JOIN residents r ON r.id = a.resident_id
            JOIN appointment_slots s ON s.id = a.slot_id
            {$where}
            ORDER BY s.slot_date DESC, s.time_start ASC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $appointments = $stmt->fetchAll();

        $this->viewAdmin('appointments/index', [
            'title' => 'Appointments',
            'appointments' => $appointments,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $perPage),
            'currentStatus' => $status,
        ]);
    }

    public function show($id) {
        $db = $this->db;
        $stmt = $db->prepare("
            SELECT a.*, r.first_name, r.last_name, r.email, r.phone,
                   s.slot_date, s.time_start, s.time_end, s.slot_type, s.max_capacity
            FROM appointments a
            JOIN residents r ON r.id = a.resident_id
            JOIN appointment_slots s ON s.id = a.slot_id
            WHERE a.id = ? AND a.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            flash('error', 'Appointment not found.');
            redirect(admin_url('appointments'));
        }

        $this->viewAdmin('appointments/show', [
            'title' => 'Appointment Details',
            'appointment' => $appointment,
        ]);
    }

    public function complete($id) {
        $db = $this->db;
        $stmt = $db->prepare("UPDATE appointments SET status = 'completed', updated_at = NOW() WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        flash('success', 'Appointment marked as completed.');
        redirect(admin_url('appointments'));
    }

    public function cancel($id) {
        $db = $this->db;
        $stmt = $db->prepare("SELECT slot_id FROM appointments WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $appt = $stmt->fetch();

        if ($appt) {
            $db->prepare("UPDATE appointments SET status = 'cancelled', updated_at = NOW() WHERE id = ?")->execute([$id]);
            $db->prepare("UPDATE appointment_slots SET current_booked = GREATEST(current_booked - 1, 0), updated_at = NOW() WHERE id = ?")->execute([$appt['slot_id']]);
        }

        flash('success', 'Appointment cancelled.');
        redirect(admin_url('appointments'));
    }

    public function delete($id) {
        $db = $this->db;
        $stmt = $db->prepare("SELECT slot_id, status FROM appointments WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            flash('error', 'Appointment not found.');
            redirect(admin_url('appointments'));
        }

        $db->prepare("UPDATE appointments SET deleted_at = NOW(), updated_at = NOW() WHERE id = ? AND deleted_at IS NULL")->execute([$id]);

        if ($appointment['status'] === 'booked') {
            $db->prepare("UPDATE appointment_slots SET current_booked = GREATEST(current_booked - 1, 0), status = IF(current_booked - 1 < max_capacity, 'open', 'full'), updated_at = NOW() WHERE id = ?")->execute([$appointment['slot_id']]);
        }

        flash('success', 'Appointment deleted.');
        redirect(admin_url('appointments'));
    }

    public function create() {
        $db = $this->db;
        $residents = $db->query("SELECT id, first_name, last_name FROM residents WHERE deleted_at IS NULL AND status = 'active' ORDER BY last_name, first_name")->fetchAll();
        $slots = $db->query("SELECT id, slot_date, time_start, time_end, max_capacity, current_booked, slot_type FROM appointment_slots WHERE deleted_at IS NULL AND status = 'open' AND current_booked < max_capacity ORDER BY slot_date ASC, time_start ASC")->fetchAll();

        $this->viewAdmin('appointments/create', [
            'title' => 'Add Appointment',
            'residents' => $residents,
            'slots' => $slots,
        ]);
    }

    public function store() {
        $input = $this->getInput();
        $errors = [];
        if (empty($input['resident_id'] ?? '')) $errors[] = 'Resident is required.';
        if (empty($input['slot_id'] ?? '')) $errors[] = 'Time slot is required.';
        if (trim($input['purpose'] ?? '') === '') $errors[] = 'Purpose is required.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('appointments/create'));
        }

        $db = $this->db;

        $slotStmt = $db->prepare("SELECT slot_date, max_capacity, current_booked, status FROM appointment_slots WHERE id = ? AND deleted_at IS NULL");
        $slotStmt->execute([$input['slot_id']]);
        $slot = $slotStmt->fetch();
        if (!$slot || $slot['status'] !== 'open' || (int)$slot['current_booked'] >= (int)$slot['max_capacity']) {
            flash('error', 'Selected slot is no longer available.');
            redirect(admin_url('appointments/create'));
        }

        $qStmt = $db->prepare("SELECT COALESCE(MAX(queue_number), 0) + 1 AS next_q FROM appointments WHERE slot_id = ? AND deleted_at IS NULL");
        $qStmt->execute([$input['slot_id']]);
        $queueNumber = (int)$qStmt->fetch()['next_q'];

        $db->beginTransaction();
        try {
            $db->prepare("INSERT INTO appointments (resident_id, slot_id, purpose, status, queue_number, created_at, updated_at) VALUES (?, ?, ?, 'booked', ?, NOW(), NOW())")
                ->execute([$input['resident_id'], $input['slot_id'], trim($input['purpose']), $queueNumber]);
            $db->prepare("UPDATE appointment_slots SET current_booked = current_booked + 1, status = IF(current_booked + 1 >= max_capacity, 'full', 'open'), updated_at = NOW() WHERE id = ?")
                ->execute([$input['slot_id']]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not book the appointment. Please try again.');
            redirect(admin_url('appointments/create'));
        }

        flash('success', 'Appointment booked successfully.');
        redirect(admin_url('appointments'));
    }

    public function slots() {
        $db = $this->db;
        $date = trim($_GET['date'] ?? '');

        $where = "WHERE deleted_at IS NULL";
        $params = [];
        if ($date !== '') {
            $where .= " AND slot_date = ?";
            $params[] = $date;
        }

        $perPage = 15;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM appointment_slots {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $db->prepare("SELECT * FROM appointment_slots {$where} ORDER BY slot_date ASC, time_start ASC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $slots = $stmt->fetchAll();

        $this->viewAdmin('appointments/slots', [
            'title' => 'Appointment Slots',
            'slots' => $slots,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $perPage),
            'currentDate' => $date,
        ]);
    }

    public function storeSlot() {
        $input = $this->getInput();
        $errors = [];
        if (empty($input['slot_date'] ?? '')) $errors[] = 'Date is required.';
        if (empty($input['time_start'] ?? '')) $errors[] = 'Start time is required.';
        if (empty($input['time_end'] ?? '')) $errors[] = 'End time is required.';
        if (($input['time_end'] ?? '') <= ($input['time_start'] ?? '')) $errors[] = 'End time must be after start time.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('appointments/slots'));
        }

        $db = $this->db;
        $stmt = $db->prepare("INSERT INTO appointment_slots (slot_date, time_start, time_end, max_capacity, current_booked, slot_type, status, created_at, updated_at) VALUES (?, ?, ?, ?, 0, ?, 'open', NOW(), NOW())");
        $stmt->execute([
            $input['slot_date'],
            $input['time_start'],
            $input['time_end'],
            (int)($input['max_capacity'] ?? 10),
            $input['slot_type'] ?? 'regular',
        ]);

        flash('success', 'Slot created successfully.');
        redirect(admin_url('appointments/slots'));
    }
}
