<?php
namespace Controllers\Admin;

class CertificateController extends \Controller {

    public function index() {
        $db = $this->db;
        $search = trim($_GET['q'] ?? '');
        $type = trim($_GET['type'] ?? '');

        $where = "WHERE c.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $where .= " AND (dt.name LIKE ? OR c.purpose LIKE ? OR CONCAT(r.first_name, ' ', r.last_name) LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($type !== '') {
            $where .= " AND c.certificate_type = ?";
            $params[] = $type;
        }

        $perPage = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM certificates c JOIN document_requests dr ON dr.id = c.request_id JOIN document_types dt ON dt.id = dr.document_type_id JOIN residents r ON r.id = dr.resident_id {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $db->prepare("
            SELECT c.*, dr.tracking_code, dr.status AS request_status, dr.purpose AS request_purpose,
                   dt.name AS type_name, CONCAT(r.first_name, ' ', r.last_name) AS resident_name,
                   r.id AS resident_id
            FROM certificates c
            JOIN document_requests dr ON dr.id = c.request_id
            JOIN document_types dt ON dt.id = dr.document_type_id
            JOIN residents r ON r.id = dr.resident_id
            {$where}
            ORDER BY c.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $certificates = $stmt->fetchAll();

        $this->viewAdmin('certificates/index', [
            'title' => 'Certificates',
            'certificates' => $certificates,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $perPage),
            'search' => $search,
            'type' => $type,
        ]);
    }

    public function create() {
        $db = $this->db;
        $residents = $db->query("SELECT id, first_name, last_name FROM residents WHERE deleted_at IS NULL AND status = 'active' ORDER BY last_name, first_name")->fetchAll();
        $docTypes = $db->query("SELECT id, name, fee FROM document_types WHERE deleted_at IS NULL AND category = 'certificate' AND is_active = 1 ORDER BY name")->fetchAll();

        $this->viewAdmin('certificates/create', [
            'title' => 'Create Certificate',
            'residents' => $residents,
            'docTypes' => $docTypes,
        ]);
    }

    public function store() {
        $input = $this->getInput();
        $errors = [];
        if (empty($input['resident_id'] ?? '')) $errors[] = 'Resident is required.';
        if (empty($input['certificate_type'] ?? '')) $errors[] = 'Certificate type is required.';
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('certificates/create'));
        }

        $db = $this->db;

        // Find or create document request
        $docTypeId = $input['document_type_id'] ?? null;
        if (!$docTypeId) {
            $dt = $db->prepare("SELECT id FROM document_types WHERE category = 'certificate' AND name LIKE ? AND deleted_at IS NULL LIMIT 1");
            $dt->execute(["%" . $input['certificate_type'] . "%"]);
            $docType = $dt->fetch();
            $docTypeId = $docType['id'] ?? 1;
        }

        $trackingCode = generate_tracking_code('CERT');
        $drStmt = $db->prepare("INSERT INTO document_requests (resident_id, document_type_id, purpose, status, tracking_code, created_at, updated_at) VALUES (?, ?, ?, 'processing', ?, NOW(), NOW())");
        $drStmt->execute([$input['resident_id'], $docTypeId, $input['purpose'] ?? '', $trackingCode]);
        $requestId = $db->lastInsertId();

        $stmt = $db->prepare("INSERT INTO certificates (request_id, certificate_type, purpose, or_number, amount, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $requestId,
            $input['certificate_type'],
            $input['purpose'] ?? '',
            $input['or_number'] ?? null,
            $input['amount'] ?? 0,
        ]);

        flash('success', 'Certificate created successfully. Tracking: ' . $trackingCode);
        redirect(admin_url('certificates'));
    }

    public function sign($id) {
        $db = $this->db;
        $stmt = $db->prepare("UPDATE certificates c JOIN document_requests dr ON dr.id = c.request_id SET dr.status = 'released', dr.released_at = NOW(), dr.updated_at = NOW() WHERE c.id = ? AND c.deleted_at IS NULL");
        $stmt->execute([$id]);
        flash('success', 'Certificate signed and released.');
        redirect(admin_url('certificates'));
    }
}
