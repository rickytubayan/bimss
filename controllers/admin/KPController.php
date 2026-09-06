<?php
namespace Controllers\Admin;

class KPController extends \Controller {

    private const NAME_SQL = "CONCAT(r.last_name, ', ', IFNULL(CONCAT(r.first_name, ' ', IFNULL(r.middle_name, '')), r.first_name))";
    private const STATUSES = ['pending_mediation', 'pending_pangkat', 'pending_conciliation', 'settled', 'repudiated', 'cfa_issued', 'barred', 'executed'];

    public function index() {
        $db = $this->db;

        $stats = [
            'cases' => (int)$db->query("SELECT COUNT(*) c FROM kp_cases WHERE deleted_at IS NULL")->fetch()['c'],
            'pending' => (int)$db->query("SELECT COUNT(*) c FROM kp_cases WHERE deleted_at IS NULL AND status IN ('pending_mediation','pending_pangkat','pending_conciliation')")->fetch()['c'],
            'settled' => (int)$db->query("SELECT COUNT(*) c FROM kp_cases WHERE deleted_at IS NULL AND status = 'settled'")->fetch()['c'],
            'cfa' => (int)$db->query("SELECT COUNT(*) c FROM kp_cfa WHERE deleted_at IS NULL AND status = 'active'")->fetch()['c'],
        ];

        $members = $db->query("
            SELECT m.*, " . self::NAME_SQL . " AS member_name
            FROM lupon_members m
            JOIN residents r ON r.id = m.resident_id
            WHERE m.deleted_at IS NULL
            ORDER BY FIELD(m.position, 'chair', 'secretary', 'member'), r.last_name
        ")->fetchAll();

        $recentCases = $db->query("
            SELECT c.*, " . self::NAME_SQL . " AS complainant_name, r2.last_name AS respondent_last_name, r2.first_name AS respondent_first_name
            FROM kp_cases c
            JOIN residents r ON r.id = c.complainant_resident_id
            JOIN residents r2 ON r2.id = c.respondent_resident_id
            WHERE c.deleted_at IS NULL
            ORDER BY c.date_filed DESC, c.id DESC LIMIT 5
        ")->fetchAll();

        $this->viewAdmin('lupon/index', [
            'title' => 'Lupon',
            'stats' => $stats,
            'members' => $members,
            'recentCases' => $recentCases,
        ]);
    }

    public function cases() {
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
                   " . self::NAME_SQL . " AS complainant_name,
                   CONCAT(r2.last_name, ', ', r2.first_name) AS respondent_name
            FROM kp_cases c
            JOIN residents r ON r.id = c.complainant_resident_id
            JOIN residents r2 ON r2.id = c.respondent_resident_id
            WHERE {$where}
            ORDER BY c.date_filed DESC, c.id DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $this->viewAdmin('lupon/cases', [
            'title' => 'Lupon - Cases',
            'rows' => $rows,
            'status' => $status,
        ]);
    }

    public function createCase() {
        $this->viewAdmin('lupon/createCase', [
            'title' => 'Lupon - New Case',
            'residents' => $this->residentOptions(),
        ]);
    }

    public function storeCase() {
        $input = $this->getInput();

        $errors = [];
        if (empty($input['complainant_resident_id'] ?? '')) $errors[] = 'Complainant is required.';
        if (empty($input['respondent_resident_id'] ?? '')) $errors[] = 'Respondent is required.';
        if ((int)($input['complainant_resident_id'] ?? 0) === (int)($input['respondent_resident_id'] ?? 0)) $errors[] = 'Complainant and respondent must be different residents.';
        if (!in_array($input['nature_of_dispute'] ?? '', ['civil', 'criminal', 'other'])) $errors[] = 'Please select a valid nature of dispute.';
        if (empty(trim($input['cause_of_action'] ?? ''))) $errors[] = 'Cause of action is required.';
        if (empty(trim($input['date_filed'] ?? ''))) $errors[] = 'Date filed is required.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('lupon/cases/create'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO kp_cases (case_number, complainant_resident_id, respondent_resident_id, nature_of_dispute, cause_of_action, date_filed, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending_mediation')
        ");
        $stmt->execute([
            generate_tracking_code('KP'),
            (int)$input['complainant_resident_id'],
            (int)$input['respondent_resident_id'],
            $input['nature_of_dispute'],
            trim($input['cause_of_action']),
            $input['date_filed'],
        ]);

        flash('success', 'Lupon case filed. Case number assigned automatically.');
        redirect(admin_url('lupon/cases'));
    }

    public function showCase($id) {
        $db = $this->db;

        $stmt = $db->prepare("
            SELECT c.*,
                   " . self::NAME_SQL . " AS complainant_name,
                   CONCAT(r2.last_name, ', ', r2.first_name) AS respondent_name
            FROM kp_cases c
            JOIN residents r ON r.id = c.complainant_resident_id
            JOIN residents r2 ON r2.id = c.respondent_resident_id
            WHERE c.id = ? AND c.deleted_at IS NULL
        ");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        if (!$row) {
            flash('error', 'Lupon case not found.');
            redirect(admin_url('lupon/cases'));
        }

        $hearings = $db->prepare("SELECT * FROM kp_hearings WHERE case_id = ? AND deleted_at IS NULL ORDER BY scheduled_date, id");
        $hearings->execute([(int)$id]);

        $settlements = $db->prepare("SELECT s.*, " . self::NAME_SQL . " AS attested_name FROM kp_settlements s LEFT JOIN residents r ON r.id = s.attested_by WHERE s.case_id = ? AND s.deleted_at IS NULL ORDER BY s.settlement_date, s.id");
        $settlements->execute([(int)$id]);

        $cfaStmt = $db->prepare("SELECT * FROM kp_cfa WHERE case_id = ? AND deleted_at IS NULL ORDER BY issued_date DESC, id DESC");
        $cfaStmt->execute([(int)$id]);

        $this->viewAdmin('lupon/showCase', [
            'title' => 'Lupon Case ' . $row['case_number'],
            'row' => $row,
            'hearings' => $hearings->fetchAll(),
            'settlements' => $settlements->fetchAll(),
            'cfaRecords' => $cfaStmt->fetchAll(),
            'residents' => $this->residentOptions(),
        ]);
    }

    public function addHearing($id) {
        $input = $this->getInput();

        $errors = [];
        if (!in_array($input['hearing_type'] ?? '', ['mediation', 'conciliation', 'arbitration'])) $errors[] = 'Please select a valid hearing type.';
        if (empty($input['scheduled_date'] ?? '')) $errors[] = 'Scheduled date is required.';
        if (($input['outcome'] ?? '') !== '' && !in_array($input['outcome'], ['settled', 'failed', 'adjourned', 'no_show_complainant', 'no_show_respondent'])) $errors[] = 'Please select a valid outcome.';

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(admin_url('lupon/cases/' . (int)$id));
        }

        $stmt = $this->db->prepare("
            INSERT INTO kp_hearings (case_id, hearing_type, scheduled_date, outcome, notes, next_schedule)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$id,
            $input['hearing_type'],
            $input['scheduled_date'],
            ($input['outcome'] ?? '') === '' ? null : $input['outcome'],
            ($input['notes'] ?? '') === '' ? null : trim($input['notes']),
            ($input['next_schedule'] ?? '') === '' ? null : $input['next_schedule'],
        ]);

        flash('success', 'Hearing recorded.');
        redirect(admin_url('lupon/cases/' . (int)$id));
    }

    public function settle($id) {
        $input = $this->getInput();

        $errors = [];
        if (!in_array($input['type'] ?? '', ['amicable_settlement', 'arbitration_award'])) $errors[] = 'Please select a valid settlement type.';
        if (empty(trim($input['details'] ?? ''))) $errors[] = 'Settlement details are required.';
        if (empty($input['settlement_date'] ?? '')) $errors[] = 'Settlement date is required.';

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(admin_url('lupon/cases/' . (int)$id));
        }

        $db = $this->db;
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO kp_settlements (case_id, type, details, signed_by_complainant, signed_by_respondent, attested_by, settlement_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                (int)$id,
                $input['type'],
                trim($input['details']),
                isset($input['signed_by_complainant']) ? 1 : 0,
                isset($input['signed_by_respondent']) ? 1 : 0,
                ($input['attested_by'] ?? '') === '' ? null : (int)$input['attested_by'],
                $input['settlement_date'],
            ]);

            $upd = $db->prepare("UPDATE kp_cases SET status = 'settled', settlement_details = ? WHERE id = ? AND deleted_at IS NULL");
            $upd->execute([trim($input['details']), (int)$id]);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not record the settlement. Please try again.');
            redirect(admin_url('lupon/cases/' . (int)$id));
        }

        flash('success', 'Case marked as settled.');
        redirect(admin_url('lupon/cases/' . (int)$id));
    }

    public function issueCFA($id) {
        $input = $this->getInput();

        $errors = [];
        if (empty($input['issued_date'] ?? '')) $errors[] = 'Issue date is required.';
        if (empty($input['valid_until'] ?? '')) $errors[] = 'Validity date is required.';

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(admin_url('lupon/cases/' . (int)$id));
        }

        $db = $this->db;
        $db->beginTransaction();
        try {
            $cert = generate_tracking_code('CFA');
            $stmt = $db->prepare("
                INSERT INTO kp_cfa (case_id, certificate_number, issued_date, valid_until, issued_by, attested_by, status)
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                (int)$id,
                $cert,
                $input['issued_date'],
                $input['valid_until'],
                ($input['issued_by'] ?? '') === '' ? null : (int)$input['issued_by'],
                ($input['attested_by'] ?? '') === '' ? null : (int)$input['attested_by'],
            ]);

            $upd = $db->prepare("UPDATE kp_cases SET status = 'cfa_issued', cfa_number = ?, cfa_date = ?, cfa_valid_until = ? WHERE id = ? AND deleted_at IS NULL");
            $upd->execute([$cert, $input['issued_date'], $input['valid_until'], (int)$id]);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not issue the Certificate of Arbitration. Please try again.');
            redirect(admin_url('lupon/cases/' . (int)$id));
        }

        flash('success', 'Certificate of Arbitration issued (' . $cert . ').');
        redirect(admin_url('lupon/cases/' . (int)$id));
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