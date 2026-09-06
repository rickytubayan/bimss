<?php
namespace Controllers\Admin;

class BookingController extends \Controller {

    private const STATUSES = ['pending', 'confirmed', 'cancelled', 'completed'];
    private const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    private const NAME_SQL = "CONCAT(r.last_name, ', ', IFNULL(CONCAT(r.first_name, ' ', IFNULL(r.middle_name, '')), r.first_name))";

    public function index() {
        $db = $this->db;

        $status = $_GET['status'] ?? '';
        $status = in_array($status, self::STATUSES) ? $status : '';

        $where = "b.deleted_at IS NULL";
        $params = [];
        if ($status !== '') {
            $where .= " AND b.status = ?";
            $params[] = $status;
        }

        $stmt = $db->prepare("
            SELECT b.*, " . self::NAME_SQL . " AS booker_name
            FROM venue_bookings b
            JOIN residents r ON r.id = b.booker_resident_id
            WHERE {$where}
            ORDER BY b.event_date DESC, b.time_start DESC, b.id DESC
        ");
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();

        $stats = [
            'total' => (int)$db->query("SELECT COUNT(*) c FROM venue_bookings WHERE deleted_at IS NULL")->fetch()['c'],
            'pending' => (int)$db->query("SELECT COUNT(*) c FROM venue_bookings WHERE deleted_at IS NULL AND status = 'pending'")->fetch()['c'],
            'confirmed' => (int)$db->query("SELECT COUNT(*) c FROM venue_bookings WHERE deleted_at IS NULL AND status = 'confirmed'")->fetch()['c'],
            'completed' => (int)$db->query("SELECT COUNT(*) c FROM venue_bookings WHERE deleted_at IS NULL AND status = 'completed'")->fetch()['c'],
            'revenue' => (float)$db->query("SELECT COALESCE(SUM(amount), 0) c FROM venue_bookings WHERE deleted_at IS NULL AND status IN ('confirmed','completed') AND payment_status = 'paid'")->fetch()['c'],
        ];

        $residents = $db->query("
            SELECT r.id, " . self::NAME_SQL . " AS name
            FROM residents r
            WHERE r.deleted_at IS NULL AND r.status = 'active'
            ORDER BY r.last_name, r.first_name
        ")->fetchAll();

        $this->viewAdmin('bookings/index', [
            'title' => 'Venue Bookings',
            'bookings' => $bookings,
            'stats' => $stats,
            'status' => $status,
            'residents' => $residents,
        ]);
    }

    public function store() {
        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty(trim($input['venue_name'] ?? ''))) $errors[] = 'Venue name is required.';
        if (empty($input['booker_resident_id'] ?? '')) $errors[] = 'Booker resident is required.';
        if (empty(trim($input['event_date'] ?? ''))) $errors[] = 'Event date is required.';
        if (empty(trim($input['time_start'] ?? ''))) $errors[] = 'Start time is required.';
        if (empty(trim($input['purpose'] ?? ''))) $errors[] = 'Purpose is required.';
        if (($input['amount'] ?? '') !== '' && (float)$input['amount'] < 0) $errors[] = 'Amount cannot be negative.';
        if (!in_array($input['payment_status'] ?? '', self::PAYMENT_STATUSES)) $errors[] = 'Please select a valid payment status.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('bookings'));
        }

        $resident = $db->prepare("SELECT id FROM residents WHERE id = ? AND deleted_at IS NULL AND status = 'active'");
        $resident->execute([(int)$input['booker_resident_id']]);
        if (!$resident->fetch()) {
            flash('error', 'Selected resident not found.');
            redirect(admin_url('bookings'));
        }

        $stmt = $db->prepare("
            INSERT INTO venue_bookings (venue_name, booker_resident_id, event_date, time_start, time_end, purpose, status, payment_status, amount)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)
        ");
        $stmt->execute([
            trim($input['venue_name']),
            (int)$input['booker_resident_id'],
            trim($input['event_date']),
            trim($input['time_start']),
            trim($input['time_end'] ?? ''),
            trim($input['purpose']),
            $input['payment_status'],
            ($input['amount'] ?? '') === '' ? 0 : (float)$input['amount'],
        ]);

        flash('success', 'Booking recorded.');
        redirect(admin_url('bookings'));
    }

    public function confirm($id) {
        $db = $this->db;
        $id = (int)$id;

        $booking = $db->prepare("SELECT * FROM venue_bookings WHERE id = ? AND deleted_at IS NULL");
        $booking->execute([$id]);
        $row = $booking->fetch();

        if (!$row) {
            flash('error', 'Booking not found.');
            redirect(admin_url('bookings'));
        }
        if ($row['status'] !== 'pending') {
            flash('error', 'Only pending bookings can be confirmed.');
            redirect(admin_url('bookings'));
        }

        $db->prepare("UPDATE venue_bookings SET status = 'confirmed' WHERE id = ? AND deleted_at IS NULL")->execute([$id]);

        flash('success', 'Booking confirmed.');
        redirect(admin_url('bookings'));
    }

    public function cancel($id) {
        $db = $this->db;
        $id = (int)$id;

        $booking = $db->prepare("SELECT * FROM venue_bookings WHERE id = ? AND deleted_at IS NULL");
        $booking->execute([$id]);
        $row = $booking->fetch();

        if (!$row) {
            flash('error', 'Booking not found.');
            redirect(admin_url('bookings'));
        }
        if (!in_array($row['status'], ['pending', 'confirmed'])) {
            flash('error', 'This booking can no longer be cancelled.');
            redirect(admin_url('bookings'));
        }

        $db->beginTransaction();
        try {
            $db->prepare("UPDATE venue_bookings SET status = 'cancelled', payment_status = 'refunded' WHERE id = ? AND deleted_at IS NULL")->execute([$id]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not cancel the booking. Please try again.');
            redirect(admin_url('bookings'));
        }

        flash('success', 'Booking cancelled.');
        redirect(admin_url('bookings'));
    }
}