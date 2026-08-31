<?php
namespace Controllers\Admin;

class ClearanceController extends \Controller {

    public function index() {
        $search = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $where = "WHERE dr.deleted_at IS NULL AND dt.category = 'clearance'";
        $params = [];

        if ($search !== '') {
            $where .= " AND (dr.tracking_code LIKE ? OR CONCAT(r.first_name, ' ', r.last_name) LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
        }
        if ($status !== '') {
            $where .= " AND dr.status = ?";
            $params[] = $status;
        }

        $perPage = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM document_requests dr
            JOIN document_types dt ON dt.id = dr.document_type_id
            JOIN residents r ON r.id = dr.resident_id
            {$where}
        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $this->db->prepare("
            SELECT dr.*, dt.name AS document_type_name, dt.fee,
                   CONCAT(r.first_name, ' ', IFNULL(CONCAT(r.middle_name, ' '), ''), r.last_name, IF(r.suffix IS NOT NULL AND r.suffix != '', CONCAT(' ', r.suffix), '')) AS resident_name,
                   r.phone AS resident_phone, r.email AS resident_email
            FROM document_requests dr
            JOIN document_types dt ON dt.id = dr.document_type_id
            JOIN residents r ON r.id = dr.resident_id
            {$where}
            ORDER BY dr.requested_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $clearances = $stmt->fetchAll();

        $totalPages = (int)ceil($total / $perPage);

        $this->viewAdmin('clearances/index', [
            'title' => 'Clearances',
            'clearances' => $clearances,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function show($id) {
        $stmt = $this->db->prepare("
            SELECT dr.*, dt.name AS document_type_name, dt.fee, dt.description AS document_description,
                   CONCAT(r.first_name, ' ', IFNULL(CONCAT(r.middle_name, ' '), ''), r.last_name, IF(r.suffix IS NOT NULL AND r.suffix != '', CONCAT(' ', r.suffix), '')) AS resident_name,
                   r.first_name, r.middle_name, r.last_name, r.suffix, r.sex, r.birthdate,
                   r.phone AS resident_phone, r.email AS resident_email, r.status AS resident_status,
                   assist.first_name AS assist_first_name, assist.last_name AS assist_last_name
            FROM document_requests dr
            JOIN document_types dt ON dt.id = dr.document_type_id
            JOIN residents r ON r.id = dr.resident_id
            LEFT JOIN residents assist ON assist.id = dr.assisted_by
            WHERE dr.id = ? AND dr.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $clearance = $stmt->fetch();

        if (!$clearance) {
            flash('error', 'Clearance not found.');
            redirect(admin_url('clearances'));
        }

        $this->viewAdmin('clearances/show', [
            'title' => 'Clearance #' . e($clearance['tracking_code']),
            'clearance' => $clearance,
        ]);
    }

    public function process($id) {
        $stmt = $this->db->prepare("UPDATE document_requests SET status = 'processing', processed_at = NOW(), updated_at = NOW() WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);

        if ($stmt->rowCount()) {
            flash('success', 'Clearance is now being processed.');
        } else {
            flash('error', 'Clearance not found.');
        }
        redirect(admin_url('clearances'));
    }

    public function sign($id) {
        $stmt = $this->db->prepare("UPDATE document_requests SET status = 'ready', updated_at = NOW() WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);

        if ($stmt->rowCount()) {
            flash('success', 'Clearance has been signed and is ready for release.');
        } else {
            flash('error', 'Clearance not found.');
        }
        redirect(admin_url('clearances'));
    }

    public function release($id) {
        $stmt = $this->db->prepare("UPDATE document_requests SET status = 'released', released_at = NOW(), updated_at = NOW() WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);

        if ($stmt->rowCount()) {
            flash('success', 'Clearance has been released.');
        } else {
            flash('error', 'Clearance not found.');
        }
        redirect(admin_url('clearances'));
    }
}
