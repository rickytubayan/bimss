<?php
namespace Controllers\Admin;

class FinanceController extends \Controller {

    public function index() {
        $db = $this->db;

        $totalIncome = (float)$db->query("SELECT COALESCE(SUM(amount), 0) total FROM income_records WHERE deleted_at IS NULL")->fetch()['total'];
        $totalExpense = (float)$db->query("SELECT COALESCE(SUM(amount), 0) total FROM expense_records WHERE deleted_at IS NULL")->fetch()['total'];
        $receiptCount = (int)$db->query("SELECT COUNT(*) c FROM official_receipts WHERE deleted_at IS NULL AND is_voided = 0")->fetch()['c'];

        $recentIncome = $db->query("SELECT * FROM income_records WHERE deleted_at IS NULL ORDER BY income_date DESC, id DESC LIMIT 8")->fetchAll();
        $recentExpenses = $db->query("SELECT * FROM expense_records WHERE deleted_at IS NULL ORDER BY expense_date DESC, id DESC LIMIT 8")->fetchAll();

        $this->viewAdmin('finance/index', [
            'title' => 'Finance',
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'receiptCount' => $receiptCount,
            'recentIncome' => $recentIncome,
            'recentExpenses' => $recentExpenses,
        ]);
    }

    public function income() {
        $db = $this->db;

        $records = $db->query("
            SELECT i.*, u.username AS collector_name
            FROM income_records i
            LEFT JOIN users u ON u.id = i.collector_id
            WHERE i.deleted_at IS NULL
            ORDER BY i.income_date DESC, i.id DESC
        ")->fetchAll();

        $total = (float)$db->query("SELECT COALESCE(SUM(amount), 0) total FROM income_records WHERE deleted_at IS NULL")->fetch()['total'];

        $this->viewAdmin('finance/income', [
            'title' => 'Finance - Income',
            'records' => $records,
            'total' => $total,
        ]);
    }

    public function storeIncome() {
        $input = $this->getInput();

        $errors = $this->validateIncome($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('finance/income'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO income_records (income_date, source, amount, or_number, collector_id, reference_no, fund_type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $input['income_date'],
            trim($input['source']),
            $input['amount'],
            ($input['or_number'] ?? '') === '' ? null : trim($input['or_number']),
            \Auth::id() ?: null,
            ($input['reference_no'] ?? '') === '' ? null : trim($input['reference_no']),
            $input['fund_type'] ?? 'general',
        ]);

        flash('success', 'Income record added successfully.');
        redirect(admin_url('finance/income'));
    }

    public function expenses() {
        $db = $this->db;

        $records = $db->query("
            SELECT e.*, u.username AS approver_name
            FROM expense_records e
            LEFT JOIN users u ON u.id = e.approved_by
            WHERE e.deleted_at IS NULL
            ORDER BY e.expense_date DESC, e.id DESC
        ")->fetchAll();

        $total = (float)$db->query("SELECT COALESCE(SUM(amount), 0) total FROM expense_records WHERE deleted_at IS NULL")->fetch()['total'];

        $this->viewAdmin('finance/expenses', [
            'title' => 'Finance - Expenses',
            'records' => $records,
            'total' => $total,
        ]);
    }

    public function storeExpense() {
        $input = $this->getInput();

        $errors = $this->validateExpense($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('finance/expenses'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO expense_records (expense_date, amount, payee, purpose, or_number, dv_number, approved_by, fund_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $input['expense_date'],
            $input['amount'],
            trim($input['payee']),
            trim($input['purpose'] ?? ''),
            ($input['or_number'] ?? '') === '' ? null : trim($input['or_number']),
            ($input['dv_number'] ?? '') === '' ? null : trim($input['dv_number']),
            \Auth::id() ?: null,
            $input['fund_type'] ?? 'general',
        ]);

        flash('success', 'Expense record added successfully.');
        redirect(admin_url('finance/expenses'));
    }

    public function receipts() {
        $db = $this->db;

        $records = $db->query("
            SELECT r.*, u.username AS printed_by_name
            FROM official_receipts r
            LEFT JOIN users u ON u.id = r.printed_by
            WHERE r.deleted_at IS NULL
            ORDER BY r.receipt_date DESC, r.id DESC
        ")->fetchAll();

        $total = (float)$db->query("SELECT COALESCE(SUM(amount), 0) total FROM official_receipts WHERE deleted_at IS NULL AND is_voided = 0")->fetch()['total'];

        $this->viewAdmin('finance/receipts', [
            'title' => 'Finance - Official Receipts',
            'records' => $records,
            'total' => $total,
        ]);
    }

    public function generateReceipt() {
        $input = $this->getInput();

        $errors = $this->validateReceipt($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('finance/receipts'));
        }

        $stmt = $this->db->prepare("SELECT COALESCE(MAX(sequence_no), 0) max_seq FROM official_receipts WHERE deleted_at IS NULL");
        $stmt->execute();
        $maxSeq = (int)$stmt->fetch()['max_seq'];
        $sequenceNo = $maxSeq + 1;

        $stmt = $this->db->prepare("
            INSERT INTO official_receipts (sequence_no, receipt_date, amount, payer, purpose, or_type, printed_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (string)$sequenceNo,
            $input['receipt_date'],
            $input['amount'],
            trim($input['payer']),
            trim($input['purpose'] ?? ''),
            $input['or_type'] ?? 'income',
            \Auth::id() ?: null,
        ]);

        flash('success', 'Official receipt #' . $sequenceNo . ' generated successfully.');
        redirect(admin_url('finance/receipts'));
    }

    private function validateIncome(array $input) {
        $errors = [];
        if (empty(trim($input['income_date'] ?? ''))) $errors[] = 'Date is required.';
        if (empty(trim($input['source'] ?? ''))) $errors[] = 'Source is required.';
        if (empty(trim($input['amount'] ?? '')) || (float)$input['amount'] <= 0) $errors[] = 'Amount must be greater than 0.';
        return $errors;
    }

    private function validateExpense(array $input) {
        $errors = [];
        if (empty(trim($input['expense_date'] ?? ''))) $errors[] = 'Date is required.';
        if (empty(trim($input['payee'] ?? ''))) $errors[] = 'Payee is required.';
        if (empty(trim($input['amount'] ?? '')) || (float)$input['amount'] <= 0) $errors[] = 'Amount must be greater than 0.';
        return $errors;
    }

    private function validateReceipt(array $input) {
        $errors = [];
        if (empty(trim($input['receipt_date'] ?? ''))) $errors[] = 'Date is required.';
        if (empty(trim($input['payer'] ?? ''))) $errors[] = 'Payer is required.';
        if (empty(trim($input['amount'] ?? '')) || (float)$input['amount'] <= 0) $errors[] = 'Amount must be greater than 0.';
        return $errors;
    }
}