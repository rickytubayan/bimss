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

    public function create() {
        $db = $this->db;
        $residents = $db->query("SELECT id, first_name, last_name FROM residents WHERE deleted_at IS NULL AND status = 'active' ORDER BY last_name, first_name")->fetchAll();
        $docTypes = $db->query("SELECT id, name, fee, description FROM document_types WHERE deleted_at IS NULL AND category = 'clearance' AND is_active = 1 ORDER BY name")->fetchAll();

        $this->viewAdmin('clearances/create', [
            'title' => 'Add Clearance',
            'residents' => $residents,
            'docTypes' => $docTypes,
        ]);
    }

    public function store() {
        $input = $this->getInput();
        $errors = [];
        if (empty($input['resident_id'] ?? '')) $errors[] = 'Resident is required.';
        if (empty($input['document_type_id'] ?? '')) $errors[] = 'Document type is required.';
        if (trim($input['purpose'] ?? '') === '') $errors[] = 'Purpose is required.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('clearances/create'));
        }

        $db = $this->db;
        $dtStmt = $db->prepare("SELECT id FROM document_types WHERE id = ? AND category = 'clearance' AND deleted_at IS NULL AND is_active = 1");
        $dtStmt->execute([$input['document_type_id']]);
        if (!$dtStmt->fetch()) {
            flash('error', 'Selected document type is not a clearance.');
            redirect(admin_url('clearances/create'));
        }

        $trackingCode = generate_tracking_code('CLR');
        $stmt = $db->prepare("INSERT INTO document_requests (resident_id, document_type_id, purpose, status, tracking_code, created_at, updated_at) VALUES (?, ?, ?, 'pending', ?, NOW(), NOW())");
        $stmt->execute([$input['resident_id'], $input['document_type_id'], trim($input['purpose']), $trackingCode]);

        flash('success', 'Clearance created successfully. Tracking: ' . $trackingCode);
        redirect(admin_url('clearances'));
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

    public function edit($id) {
        $db = $this->db;
        $stmt = $db->prepare("
            SELECT dr.*, dt.name AS document_type_name
            FROM document_requests dr
            JOIN document_types dt ON dt.id = dr.document_type_id
            WHERE dr.id = ? AND dr.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $clearance = $stmt->fetch();

        if (!$clearance) {
            flash('error', 'Clearance not found.');
            redirect(admin_url('clearances'));
        }

        $residents = $db->query("SELECT id, first_name, last_name FROM residents WHERE deleted_at IS NULL AND status = 'active' ORDER BY last_name, first_name")->fetchAll();
        $docTypes = $db->query("SELECT id, name, fee FROM document_types WHERE deleted_at IS NULL AND category = 'clearance' AND is_active = 1 ORDER BY name")->fetchAll();

        $this->viewAdmin('clearances/edit', [
            'title' => 'Edit Clearance #' . $clearance['tracking_code'],
            'clearance' => $clearance,
            'residents' => $residents,
            'docTypes' => $docTypes,
        ]);
    }

    public function update($id) {
        $input = $this->getInput();
        $errors = [];
        if (empty($input['resident_id'] ?? '')) $errors[] = 'Resident is required.';
        if (empty($input['document_type_id'] ?? '')) $errors[] = 'Document type is required.';
        if (trim($input['purpose'] ?? '') === '') $errors[] = 'Purpose is required.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('clearances/' . $id . '/edit'));
        }

        $db = $this->db;

        $exists = $db->prepare("SELECT id FROM document_requests WHERE id = ? AND deleted_at IS NULL");
        $exists->execute([$id]);
        if (!$exists->fetch()) {
            flash('error', 'Clearance not found.');
            redirect(admin_url('clearances'));
        }

        $dtStmt = $db->prepare("SELECT id FROM document_types WHERE id = ? AND category = 'clearance' AND deleted_at IS NULL AND is_active = 1");
        $dtStmt->execute([$input['document_type_id']]);
        if (!$dtStmt->fetch()) {
            flash('error', 'Selected document type is not a clearance.');
            redirect(admin_url('clearances/' . $id . '/edit'));
        }

        $requestStatus = in_array($input['status'] ?? '', ['pending', 'processing', 'for_signing', 'ready', 'released', 'cancelled'], true) ? $input['status'] : 'pending';
        $releasedTo = $input['released_to'] !== '' ? $input['released_to'] : null;
        $representative = $input['representative_name'] !== '' ? $input['representative_name'] : null;

        $stmt = $db->prepare("UPDATE document_requests SET resident_id = ?, document_type_id = ?, purpose = ?, status = ?, released_to = ?, representative_name = ?, updated_at = NOW() WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([
            $input['resident_id'],
            $input['document_type_id'],
            trim($input['purpose']),
            $requestStatus,
            $releasedTo,
            $representative,
            $id,
        ]);

        flash('success', 'Clearance updated successfully.');
        redirect(admin_url('clearances/' . $id));
    }

    public function delete($id) {
        $stmt = $this->db->prepare("SELECT id FROM document_requests WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            flash('error', 'Clearance not found.');
            redirect(admin_url('clearances'));
        }

        $this->db->prepare("UPDATE document_requests SET deleted_at = NOW(), updated_at = NOW() WHERE id = ? AND deleted_at IS NULL")->execute([$id]);

        flash('success', 'Clearance deleted.');
        redirect(admin_url('clearances'));
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
