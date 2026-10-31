<?php
namespace Controllers\Admin;

class DashboardController extends \Controller {
    public function index() {
        $db = $this->db;

        $stats = [
            'residents' => (int)$db->query("SELECT COUNT(*) c FROM residents WHERE deleted_at IS NULL AND status = 'active'")->fetch()['c'],
            'households' => (int)$db->query("SELECT COUNT(*) c FROM households WHERE deleted_at IS NULL")->fetch()['c'],
            'pending_requests' => (int)$db->query("SELECT COUNT(*) c FROM document_requests WHERE deleted_at IS NULL AND status IN ('pending','processing','for_signing')")->fetch()['c'],
            'appointments_today' => (int)$db->query("
                SELECT COUNT(*) c
                FROM appointments a
                JOIN appointment_slots s ON s.id = a.slot_id
                WHERE a.deleted_at IS NULL AND a.status = 'booked' AND s.slot_date = CURDATE()
            ")->fetch()['c'],
            'revenue_month' => (float)$db->query("SELECT COALESCE(SUM(amount),0) c FROM income_records WHERE deleted_at IS NULL AND YEAR(income_date) = YEAR(CURDATE()) AND MONTH(income_date) = MONTH(CURDATE())")->fetch()['c'],
            'expenses_month' => (float)$db->query("SELECT COALESCE(SUM(amount),0) c FROM expense_records WHERE deleted_at IS NULL AND YEAR(expense_date) = YEAR(CURDATE()) AND MONTH(expense_date) = MONTH(CURDATE())")->fetch()['c'],
            'seniors_pwd' => (int)$db->query("SELECT COUNT(*) c FROM senior_pwd_profiles WHERE deleted_at IS NULL")->fetch()['c'],
            'pregnant' => (int)$db->query("SELECT COUNT(*) c FROM maternal_records WHERE deleted_at IS NULL AND status = 'pregnant'")->fetch()['c'],
            'total_revenue' => (float)$db->query("SELECT COALESCE(SUM(amount),0) c FROM income_records WHERE deleted_at IS NULL")->fetch()['c'],
            'total_expenses' => (float)$db->query("SELECT COALESCE(SUM(amount),0) c FROM expense_records WHERE deleted_at IS NULL")->fetch()['c'],
        ];

        $health = [
            'maternal' => (int)$db->query("SELECT COUNT(*) c FROM maternal_records WHERE deleted_at IS NULL")->fetch()['c'],
            'pregnant' => $stats['pregnant'],
            'immunization' => (int)$db->query("SELECT COUNT(*) c FROM immunization_records WHERE deleted_at IS NULL")->fetch()['c'],
            'growth' => (int)$db->query("SELECT COUNT(*) c FROM child_growth_records WHERE deleted_at IS NULL")->fetch()['c'],
            'surveillance' => (int)$db->query("SELECT COUNT(*) c FROM disease_surveillance WHERE deleted_at IS NULL")->fetch()['c'],
            'active_surveillance' => (int)$db->query("SELECT COUNT(*) c FROM disease_surveillance WHERE deleted_at IS NULL AND status IN ('suspected','confirmed')")->fetch()['c'],
            'programs' => (int)$db->query("SELECT COUNT(*) c FROM health_programs WHERE deleted_at IS NULL")->fetch()['c'],
            'seniors_pwd' => $stats['seniors_pwd'],
        ];

        $recentRequests = $db->query("
            SELECT dr.*, dt.name AS doc_type, CONCAT(r.last_name, ', ', r.first_name) AS resident_name
            FROM document_requests dr
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            LEFT JOIN residents r ON r.id = dr.resident_id
            WHERE dr.deleted_at IS NULL
            ORDER BY dr.created_at DESC, dr.id DESC
            LIMIT 6
        ")->fetchAll();

        $recentHealthPrograms = $db->query("
            SELECT * FROM health_programs
            WHERE deleted_at IS NULL
            ORDER BY schedule_date DESC, id DESC
            LIMIT 5
        ")->fetchAll();

        $incomeRows = $db->query("
            SELECT MONTH(income_date) AS m, COALESCE(SUM(amount),0) AS total
            FROM income_records
            WHERE deleted_at IS NULL AND YEAR(income_date) = YEAR(CURDATE())
            GROUP BY MONTH(income_date)
        ")->fetchAll();

        $expenseRows = $db->query("
            SELECT MONTH(expense_date) AS m, COALESCE(SUM(amount),0) AS total
            FROM expense_records
            WHERE deleted_at IS NULL AND YEAR(expense_date) = YEAR(CURDATE())
            GROUP BY MONTH(expense_date)
        ")->fetchAll();

        $incomeByMonth = array_fill(1, 12, 0.0);
        foreach ($incomeRows as $row) {
            $incomeByMonth[(int)$row['m']] = (float)$row['total'];
        }

        $expenseByMonth = array_fill(1, 12, 0.0);
        foreach ($expenseRows as $row) {
            $expenseByMonth[(int)$row['m']] = (float)$row['total'];
        }

        $monthlyFinance = [];
        $financeMax = 1.0;
        for ($m = 1; $m <= 12; $m++) {
            $income = $incomeByMonth[$m];
            $expense = $expenseByMonth[$m];
            $financeMax = max($financeMax, $income, $expense);
            $monthlyFinance[] = [
                'month' => date('M', mktime(0, 0, 0, $m, 1)),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        }

        $this->viewAdmin('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'health' => $health,
            'recentRequests' => $recentRequests,
            'recentHealthPrograms' => $recentHealthPrograms,
            'monthlyFinance' => $monthlyFinance,
            'financeMax' => $financeMax,
        ]);
    }
}
