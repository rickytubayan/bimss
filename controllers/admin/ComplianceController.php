<?php
namespace Controllers\Admin;

class ComplianceController extends \Controller {

    private const DOCUMENT_TYPES = ['budget', 'income_expenditure', 'nta_utilization', 'procurement', 'awards', 'monthly_collections', 'annual_report'];
    private const STATUSES = ['posted', 'expired', 'removed'];

    private const TYPE_LABELS = [
        'budget' => 'Annual Budget',
        'income_expenditure' => 'Income & Expenditure',
        'nta_utilization' => 'NTA Utilization',
        'procurement' => 'Procurement',
        'awards' => 'Awards & Contracts',
        'monthly_collections' => 'Monthly Collections',
        'annual_report' => 'Annual Report',
    ];

    public function index() {
        $db = $this->db;

        $type = $_GET['type'] ?? '';
        $type = array_key_exists($type, self::TYPE_LABELS) ? $type : '';

        $where = "t.deleted_at IS NULL";
        $params = [];
        if ($type !== '') {
            $where .= " AND t.document_type = ?";
            $params[] = $type;
        }

        $stmt = $db->prepare("
            SELECT * FROM transparency_documents t
            WHERE {$where}
            ORDER BY t.fiscal_year DESC, t.posted_at DESC, t.id DESC
        ");
        $stmt->execute($params);
        $documents = $stmt->fetchAll();

        $stats = [
            'documents' => (int)$db->query("SELECT COUNT(*) c FROM transparency_documents WHERE deleted_at IS NULL")->fetch()['c'],
            'posted' => (int)$db->query("SELECT COUNT(*) c FROM transparency_documents WHERE deleted_at IS NULL AND status = 'posted'")->fetch()['c'],
            'years' => (int)$db->query("SELECT COUNT(DISTINCT fiscal_year) c FROM transparency_documents WHERE deleted_at IS NULL")->fetch()['c'],
            'needs_posting' => (int)$db->query("
                SELECT COUNT(*) c FROM transparency_documents
                WHERE deleted_at IS NULL
                  AND status <> 'removed'
                  AND (valid_until IS NOT NULL AND valid_until < CURDATE())
            ")->fetch()['c'],
        ];

        $covered = $db->query("
            SELECT DISTINCT document_type FROM transparency_documents
            WHERE deleted_at IS NULL AND status = 'posted'
        ")->fetchAll(\PDO::FETCH_COLUMN);

        $this->viewAdmin('compliance/index', [
            'title' => 'Compliance',
            'documents' => $documents,
            'stats' => $stats,
            'type' => $type,
            'typeLabels' => self::TYPE_LABELS,
            'covered' => $covered,
        ]);
    }

    public function transparency() {
        $this->index();
    }

    public function uploadDocument() {
        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty(trim($input['title'] ?? ''))) $errors[] = 'Document title is required.';
        if (!array_key_exists($input['document_type'] ?? '', self::TYPE_LABELS)) $errors[] = 'Please select a valid document type.';
        if (empty($input['fiscal_year'] ?? '')) $errors[] = 'Fiscal year is required.';
        if (!in_array($input['status'] ?? '', self::STATUSES)) $errors[] = 'Please select a valid status.';

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Please choose a file to upload.';
        }

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('compliance'));
        }

        $file = $_FILES['file'];
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            flash('error', 'File type not allowed. Use PDF, Word, Excel, or image files.');
            redirect(admin_url('compliance'));
        }

        $config = require __DIR__ . '/../../config/app.php';
        $uploadDir = rtrim($config['upload_path'], '/\\') . DIRECTORY_SEPARATOR . 'transparency' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $storedName = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $uploadDir . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            flash('error', 'Could not save the uploaded file. Please try again.');
            redirect(admin_url('compliance'));
        }

        $stmt = $db->prepare("
            INSERT INTO transparency_documents (document_type, fiscal_year, quarter, file_path, title, valid_until, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $input['document_type'],
            (int)$input['fiscal_year'],
            ($input['quarter'] ?? '') === '' ? null : (int)$input['quarter'],
            'uploads/transparency/' . $storedName,
            trim($input['title']),
            trim($input['valid_until'] ?? '') === '' ? null : trim($input['valid_until']),
            $input['status'],
        ]);

        flash('success', 'Document uploaded and posted.');
        redirect(admin_url('compliance'));
    }
}