<?php
namespace Controllers\Admin;

class ReportController extends \Controller {

    private const FUND_TYPES = ['general', 'development', 'sk', 'drrm', 'gad'];

    public function index() {
        $db = $this->db;

        $years = $this->availableYears();
        $year = $this->selectedYear($years);

        $stats = [
            'income' => (float)$db->query("SELECT COALESCE(SUM(amount),0) t FROM income_records WHERE deleted_at IS NULL AND YEAR(income_date) = {$year}")->fetch()['t'],
            'expense' => (float)$db->query("SELECT COALESCE(SUM(amount),0) t FROM expense_records WHERE deleted_at IS NULL AND YEAR(expense_date) = {$year}")->fetch()['t'],
            'budget' => (float)$db->query("SELECT COALESCE(SUM(total_amount),0) t FROM budgets WHERE deleted_at IS NULL AND fiscal_year = {$year} AND status = 'approved'")->fetch()['t'],
        ];
        $stats['net'] = $stats['income'] - $stats['expense'];

        $expensesByFund = $db->query("
            SELECT fund_type, COUNT(*) c, COALESCE(SUM(amount),0) t
            FROM expense_records WHERE deleted_at IS NULL AND YEAR(expense_date) = {$year}
            GROUP BY fund_type
        ")->fetchAll();

        $this->viewAdmin('reports/index', [
            'title' => 'Reports',
            'year' => $year,
            'years' => $years,
            'stats' => $stats,
            'expensesByFund' => $expensesByFund,
        ]);
    }

    public function annual() {
        $db = $this->db;

        $years = $this->availableYears();
        $year = $this->selectedYear($years);

        $income = (float)$db->query("SELECT COALESCE(SUM(amount),0) t FROM income_records WHERE deleted_at IS NULL AND YEAR(income_date) = {$year}")->fetch()['t'];
        $expense = (float)$db->query("SELECT COALESCE(SUM(amount),0) t FROM expense_records WHERE deleted_at IS NULL AND YEAR(expense_date) = {$year}")->fetch()['t'];
        $approvedBudget = (float)$db->query("SELECT COALESCE(SUM(total_amount),0) t FROM budgets WHERE deleted_at IS NULL AND fiscal_year = {$year} AND status = 'approved'")->fetch()['t'];

        $incomeByFund = $db->query("
            SELECT fund_type, COUNT(*) c, COALESCE(SUM(amount),0) t
            FROM income_records WHERE deleted_at IS NULL AND YEAR(income_date) = {$year}
            GROUP BY fund_type
        ")->fetchAll();

        $expenseByFund = $db->query("
            SELECT fund_type, COUNT(*) c, COALESCE(SUM(amount),0) t
            FROM expense_records WHERE deleted_at IS NULL AND YEAR(expense_date) = {$year}
            GROUP BY fund_type
        ")->fetchAll();

        $monthlyIncome = $db->query("
            SELECT MONTH(income_date) m, MONTHNAME(income_date) name, COALESCE(SUM(amount),0) t
            FROM income_records WHERE deleted_at IS NULL AND YEAR(income_date) = {$year}
            GROUP BY MONTH(income_date) ORDER BY m
        ")->fetchAll();

        $monthlyExpense = $db->query("
            SELECT MONTH(expense_date) m, MONTHNAME(expense_date) name, COALESCE(SUM(amount),0) t
            FROM expense_records WHERE deleted_at IS NULL AND YEAR(expense_date) = {$year}
            GROUP BY MONTH(expense_date) ORDER BY m
        ")->fetchAll();

        $serviceStats = [
            'residents' => (int)$db->query("SELECT COUNT(*) c FROM residents WHERE deleted_at IS NULL AND YEAR(created_at) = {$year}")->fetch()['c'],
            'seniors_pwd' => (int)$db->query("SELECT COUNT(*) c FROM senior_pwd_profiles WHERE deleted_at IS NULL AND YEAR(created_at) = {$year}")->fetch()['c'],
            'clearances' => (int)$db->query("SELECT COUNT(*) c FROM barangay_clearances WHERE deleted_at IS NULL AND YEAR(created_at) = {$year}")->fetch()['c'],
            'certificates' => (int)$db->query("SELECT COUNT(*) c FROM certificates WHERE deleted_at IS NULL AND YEAR(created_at) = {$year}")->fetch()['c'],
            'blotters' => (int)$db->query("SELECT COUNT(*) c FROM blotters WHERE deleted_at IS NULL AND YEAR(created_at) = {$year}")->fetch()['c'],
            'complaints' => (int)$db->query("SELECT COUNT(*) c FROM complaints WHERE deleted_at IS NULL AND YEAR(created_at) = {$year}")->fetch()['c'],
            'donations' => (float)$db->query("SELECT COALESCE(SUM(amount),0) t FROM donation_records WHERE deleted_at IS NULL AND YEAR(received_at) = {$year}")->fetch()['t'],
            'tax_collected' => (float)$db->query("SELECT COALESCE(SUM(amount_paid),0) t FROM tax_ledgers WHERE deleted_at IS NULL AND YEAR(payment_date) = {$year}")->fetch()['t'],
        ];

        $annualDocument = $db->prepare("
            SELECT * FROM transparency_documents
            WHERE deleted_at IS NULL AND document_type = 'annual_report' AND fiscal_year = ?
            ORDER BY posted_at DESC, id DESC LIMIT 1
        ");
        $annualDocument->execute([$year]);
        $annualDocument = $annualDocument->fetch();

        $this->viewAdmin('reports/annual', [
            'title' => 'Annual Report ' . $year,
            'year' => $year,
            'years' => $years,
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'approvedBudget' => $approvedBudget,
            'incomeByFund' => $incomeByFund,
            'expenseByFund' => $expenseByFund,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'serviceStats' => $serviceStats,
            'annualDocument' => $annualDocument,
        ]);
    }

    public function soba() {
        $db = $this->db;

        $years = $this->availableYears();
        $year = $this->selectedYear($years);

        $allocations = $db->prepare("
            SELECT * FROM statutory_allocations
            WHERE deleted_at IS NULL AND fiscal_year = ?
            ORDER BY id
        ");
        $allocations->execute([$year]);
        $allocations = $allocations->fetchAll();

        $budgets = $db->query("SELECT * FROM budgets WHERE deleted_at IS NULL AND fiscal_year = {$year} ORDER BY fund_type")->fetchAll();
        $budgetsByFund = [];
        foreach ($budgets as $b) {
            $budgetsByFund[$b['fund_type']][] = $b;
        }

        $this->viewAdmin('reports/soba', [
            'title' => 'SOBA Report ' . $year,
            'year' => $year,
            'years' => $years,
            'allocations' => $allocations,
            'budgets' => $budgets,
            'budgetsByFund' => $budgetsByFund,
        ]);
    }

    public function budget() {
        $db = $this->db;

        $year = (int)($_GET['year'] ?? 0);
        if ($year <= 0) $year = (int)date('Y');
        $years = $db->query("SELECT DISTINCT fiscal_year fy FROM budgets WHERE deleted_at IS NULL ORDER BY fy DESC")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($years)) $years = [(int)date('Y')];
        if (!in_array($year, $years, true)) $year = (int)$years[0];

        $budgets = [];
        $rows = $db->query("
            SELECT b.*, c.code, c.name account_name, c.type account_type,
                   li.allocated_amount, li.utilized_amount, li.description item_desc,
                   li.id line_id
            FROM budgets b
            LEFT JOIN budget_line_items li ON li.budget_id = b.id AND li.deleted_at IS NULL
            LEFT JOIN chart_of_accounts c ON c.id = li.account_id
            WHERE b.deleted_at IS NULL AND b.fiscal_year = {$year}
            ORDER BY b.fund_type, b.id, c.code
        ")->fetchAll();

        foreach ($rows as $row) {
            $budgets[$row['id']] = $budgets[$row['id']] ?? [
                'id' => $row['id'],
                'fiscal_year' => $row['fiscal_year'],
                'fund_type' => $row['fund_type'],
                'status' => $row['status'],
                'total_amount' => $row['total_amount'],
                'items' => [],
            ];
            if ($row['line_id']) {
                $budgets[$row['id']]['items'][] = $row;
            }
        }

        $totals = [
            'allocated' => (float)$db->query("SELECT COALESCE(SUM(allocated_amount),0) t FROM budget_line_items li JOIN budgets b ON b.id = li.budget_id WHERE li.deleted_at IS NULL AND b.deleted_at IS NULL AND b.fiscal_year = {$year}")->fetch()['t'],
            'utilized' => (float)$db->query("SELECT COALESCE(SUM(utilized_amount),0) t FROM budget_line_items li JOIN budgets b ON b.id = li.budget_id WHERE li.deleted_at IS NULL AND b.deleted_at IS NULL AND b.fiscal_year = {$year}")->fetch()['t'],
        ];
        $totals['balance'] = $totals['allocated'] - $totals['utilized'];

        $this->viewAdmin('reports/budget', [
            'title' => 'Budget Report ' . $year,
            'year' => $year,
            'years' => $years,
            'budgets' => $budgets,
            'totals' => $totals,
        ]);
    }

    private function availableYears() {
        $db = $this->db;
        $years = $db->query("
            SELECT fiscal_year FROM budgets WHERE deleted_at IS NULL
            UNION SELECT YEAR(income_date) FROM income_records WHERE deleted_at IS NULL
            UNION SELECT YEAR(expense_date) FROM expense_records WHERE deleted_at IS NULL
            UNION SELECT fiscal_year FROM statutory_allocations WHERE deleted_at IS NULL
            UNION SELECT fiscal_year FROM transparency_documents WHERE deleted_at IS NULL
        ")->fetchAll(\PDO::FETCH_COLUMN);
        $years = array_filter($years, fn($y) => $y > 2000);
        $years = array_map('intval', $years);
        $years[] = (int)date('Y');
        $years = array_values(array_unique($years));
        rsort($years);
        return $years;
    }

    private function selectedYear(array $years) {
        $year = (int)($_GET['year'] ?? 0);
        if ($year <= 0 || !in_array($year, $years, true)) $year = $years[0];
        return $year;
    }
}