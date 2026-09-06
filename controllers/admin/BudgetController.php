<?php
namespace Controllers\Admin;

class BudgetController extends \Controller {

    public function index() {
        $db = $this->db;

        $budgets = $db->query("
            SELECT b.*,
                   COALESCE(SUM(li.allocated_amount), 0) AS total_allocated,
                   COALESCE(SUM(li.utilized_amount), 0) AS total_utilized,
                   COUNT(li.id) AS line_count
            FROM budgets b
            LEFT JOIN budget_line_items li ON li.budget_id = b.id
            WHERE b.deleted_at IS NULL
            GROUP BY b.id
            ORDER BY b.fiscal_year DESC, b.id DESC
        ")->fetchAll();

        $this->viewAdmin('budget/index', [
            'title' => 'Budget',
            'budgets' => $budgets,
        ]);
    }

    public function create() {
        $db = $this->db;
        $accounts = $db->query("
            SELECT * FROM chart_of_accounts
            WHERE is_active = 1 AND type IN ('income', 'expense')
            ORDER BY type, code
        ")->fetchAll();

        $currentYear = (int)date('Y');

        $this->viewAdmin('budget/create', [
            'title' => 'Create Budget',
            'accounts' => $accounts,
            'currentYear' => $currentYear,
        ]);
    }

    public function store() {
        $input = $this->getInput();

        $errors = $this->validateBudget($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('budget/create'));
        }

        $db = $this->db;
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("
                INSERT INTO budgets (fiscal_year, fund_type, total_amount, status)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                (int)$input['fiscal_year'],
                $input['fund_type'],
                $input['total_amount'],
                $input['status'] ?? 'draft',
            ]);
            $budgetId = $db->lastInsertId();

            $accounts = $input['account_id'] ?? [];
            $allocated = $input['allocated_amount'] ?? [];
            $descriptions = $input['description'] ?? [];

            $itemStmt = $db->prepare("
                INSERT INTO budget_line_items (budget_id, account_id, allocated_amount, utilized_amount, description)
                VALUES (?, ?, ?, 0.00, ?)
            ");

            foreach ($accounts as $i => $accountId) {
                if (empty($accountId)) continue;
                $amount = isset($allocated[$i]) ? (float)$allocated[$i] : 0.00;
                if ($amount <= 0) continue;
                $desc = isset($descriptions[$i]) ? trim($descriptions[$i]) : null;
                $itemStmt->execute([$budgetId, (int)$accountId, $amount, ($desc === '') ? null : $desc]);
            }

            $db->commit();
            flash('success', 'Budget created successfully.');
            redirect(admin_url('budget/' . $budgetId));
        } catch (\Throwable $e) {
            $db->rollBack();
            flash('error', 'Failed to create budget: ' . $e->getMessage());
            redirect(admin_url('budget/create'));
        }
    }

    public function show($id) {
        $db = $this->db;

        $stmt = $db->prepare("SELECT * FROM budgets WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $budget = $stmt->fetch();

        if (!$budget) {
            flash('error', 'Budget not found.');
            redirect(admin_url('budget'));
        }

        $stmt = $db->prepare("
            SELECT li.*, c.code, c.name, c.type
            FROM budget_line_items li
            JOIN chart_of_accounts c ON c.id = li.account_id
            WHERE li.budget_id = ? AND li.deleted_at IS NULL
            ORDER BY c.type, c.code
        ");
        $stmt->execute([$id]);
        $lineItems = $stmt->fetchAll();

        $totalAllocated = 0.0;
        $totalUtilized = 0.0;
        foreach ($lineItems as $li) {
            $totalAllocated += (float)$li['allocated_amount'];
            $totalUtilized += (float)$li['utilized_amount'];
        }

        $this->viewAdmin('budget/show', [
            'title' => 'Budget ' . $budget['fiscal_year'] . ' - ' . strtoupper($budget['fund_type']),
            'budget' => $budget,
            'lineItems' => $lineItems,
            'totalAllocated' => $totalAllocated,
            'totalUtilized' => $totalUtilized,
        ]);
    }

    private function validateBudget(array $input) {
        $errors = [];
        if (empty(trim($input['fiscal_year'] ?? ''))) $errors[] = 'Fiscal year is required.';
        if (empty(trim($input['fund_type'] ?? ''))) $errors[] = 'Fund type is required.';
        if (empty(trim($input['total_amount'] ?? '')) || (float)$input['total_amount'] <= 0) $errors[] = 'Total budget amount must be greater than 0.';

        $accounts = $input['account_id'] ?? [];
        $allocated = $input['allocated_amount'] ?? [];
        $hasItems = false;
        foreach ($accounts as $i => $accountId) {
            if (!empty($accountId) && isset($allocated[$i]) && (float)$allocated[$i] > 0) {
                $hasItems = true;
                break;
            }
        }
        if (!$hasItems) $errors[] = 'Add at least one budget line item with an allocated amount.';
        return $errors;
    }
}