<?php
namespace Controllers\Admin;

class TaxController extends \Controller {

    public function index() {
        $db = $this->db;

        $totalAssessed = (float)$db->query("SELECT COALESCE(SUM(assessed_value), 0) total FROM tax_ledgers WHERE deleted_at IS NULL")->fetch()['total'];
        $totalDue = (float)$db->query("SELECT COALESCE(SUM(amount_due + penalties), 0) total FROM tax_ledgers WHERE deleted_at IS NULL")->fetch()['total'];
        $totalPaid = (float)$db->query("SELECT COALESCE(SUM(amount_paid), 0) total FROM tax_ledgers WHERE deleted_at IS NULL")->fetch()['total'];
        $delinquentCount = (int)$db->query("SELECT COUNT(*) c FROM tax_ledgers WHERE deleted_at IS NULL AND status = 'delinquent'")->fetch()['c'];

        $rows = $db->query("
            SELECT t.*,
                   (t.amount_due + t.penalties - t.amount_paid) AS balance
            FROM tax_ledgers t
            WHERE t.deleted_at IS NULL
            ORDER BY t.due_date ASC, t.id DESC
        ")->fetchAll();

        foreach ($rows as &$r) {
            $r['balance'] = (float)$r['balance'];
        }
        unset($r);

        $this->viewAdmin('tax/index', [
            'title' => 'Tax Ledger',
            'totalAssessed' => $totalAssessed,
            'totalDue' => $totalDue,
            'totalPaid' => $totalPaid,
            'delinquentCount' => $delinquentCount,
            'rows' => $rows,
        ]);
    }

    public function create() {
        $this->viewAdmin('tax/create', ['title' => 'Tax Ledger - Add Entry']);
    }

    public function store() {
        $input = $this->getInput();

        $errors = $this->validateLedger($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('tax/create'));
        }

        $dueDate = $input['due_date'];
        $status = ($dueDate < date('Y-m-d')) ? 'delinquent' : 'unpaid';

        $stmt = $this->db->prepare("
            INSERT INTO tax_ledgers (taxpayer_name, tax_type, assessed_value, amount_due, penalties, due_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($input['taxpayer_name']),
            $input['tax_type'],
            (float)($input['assessed_value'] ?? 0),
            (float)$input['amount_due'],
            (float)($input['penalties'] ?? 0),
            $dueDate,
            $status,
        ]);

        flash('success', 'Tax entry added successfully.');
        redirect(admin_url('tax'));
    }

    public function recordPayment($id) {
        $input = $this->getInput();

        $stmt = $this->db->prepare("SELECT * FROM tax_ledgers WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $ledger = $stmt->fetch();

        if (!$ledger) {
            flash('error', 'Tax entry not found.');
            redirect(admin_url('tax'));
        }

        $errors = [];
        if (empty(trim($input['amount'] ?? '')) || (float)$input['amount'] <= 0) $errors[] = 'Payment amount must be greater than 0.';
        if (empty(trim($input['payment_date'] ?? ''))) $errors[] = 'Payment date is required.';

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(admin_url('tax'));
        }

        $balance = (float)$ledger['amount_due'] + (float)$ledger['penalties'] - (float)$ledger['amount_paid'];
        $payment = (float)$input['amount'];
        if ($payment > $balance) {
            flash('error', 'Payment amount exceeds the remaining balance.');
            redirect(admin_url('tax'));
        }

        $newPaid = (float)$ledger['amount_paid'] + $payment;
        $newBalance = (float)$ledger['amount_due'] + (float)$ledger['penalties'] - $newPaid;

        if ($newBalance <= 0.005) {
            $status = 'paid';
        } elseif ($ledger['due_date'] < date('Y-m-d')) {
            $status = 'delinquent';
        } else {
            $status = 'partial';
        }

        $stmt = $this->db->prepare("
            UPDATE tax_ledgers
            SET amount_paid = ?, payment_date = ?, or_number = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $newPaid,
            $input['payment_date'],
            ($input['or_number'] ?? '') === '' ? null : trim($input['or_number']),
            $status,
            $id,
        ]);

        flash('success', 'Payment recorded successfully.');
        redirect(admin_url('tax'));
    }

    private function validateLedger(array $input) {
        $errors = [];
        if (empty(trim($input['taxpayer_name'] ?? ''))) $errors[] = 'Taxpayer name is required.';
        if (!in_array($input['tax_type'] ?? '', ['rpt', 'business'])) $errors[] = 'Please select a valid tax type.';
        if (empty(trim($input['amount_due'] ?? '')) || (float)$input['amount_due'] <= 0) $errors[] = 'Amount due must be greater than 0.';
        if (empty(trim($input['due_date'] ?? ''))) $errors[] = 'Due date is required.';
        if (($input['assessed_value'] ?? '') !== '' && (float)$input['assessed_value'] < 0) $errors[] = 'Assessed value cannot be negative.';
        if (($input['penalties'] ?? '') !== '' && (float)$input['penalties'] < 0) $errors[] = 'Penalties cannot be negative.';
        return $errors;
    }
}