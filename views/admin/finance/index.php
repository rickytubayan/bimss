<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Finance</h1>
        <p class="page-subtitle mb-0">Income, expenses, and official receipts</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('finance/income') ?>" class="btn btn-success"><i class="bi bi-box-arrow-in-down me-1"></i>Record Income</a>
        <a href="<?= admin_url('finance/expenses') ?>" class="btn btn-danger"><i class="bi bi-box-arrow-up me-1"></i>Record Expense</a>
        <a href="<?= admin_url('finance/receipts') ?>" class="btn btn-primary"><i class="bi bi-receipt me-1"></i>Generate Receipt</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-box-arrow-in-down"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Income"><?= format_currency($totalIncome) ?></div>
            <div class="stat-label">Total Income</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-box-arrow-up"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Expenses"><?= format_currency($totalExpense) ?></div>
            <div class="stat-label">Total Expenses</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon <?= ($totalIncome - $totalExpense) < 0 ? 'yellow' : 'blue' ?> d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Net Position"><?= format_currency($totalIncome - $totalExpense) ?></div>
            <div class="stat-label">Net Position</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Official Receipts"><?= number_format($receiptCount) ?></div>
            <div class="stat-label">Official Receipts</div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Recent Income</span>
                <a href="<?= admin_url('finance/income') ?>" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Date</th><th>Source</th><th>Fund</th><th class="text-end">Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentIncome as $r): ?>
                            <tr>
                                <td class="text-muted small"><?= format_date($r['income_date']) ?></td>
                                <td><?= e($r['source']) ?></td>
                                <td><span class="text-capitalize small"><?= e($r['fund_type']) ?></span></td>
                                <td class="text-end fw-semibold text-success"><?= format_currency($r['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentIncome)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No income records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-box-arrow-up me-2 text-danger"></i>Recent Expenses</span>
                <a href="<?= admin_url('finance/expenses') ?>" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Date</th><th>Payee</th><th>Fund</th><th class="text-end">Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentExpenses as $r): ?>
                            <tr>
                                <td class="text-muted small"><?= format_date($r['expense_date']) ?></td>
                                <td><?= e($r['payee']) ?></td>
                                <td><span class="text-capitalize small"><?= e($r['fund_type']) ?></span></td>
                                <td class="text-end fw-semibold text-danger"><?= format_currency($r['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentExpenses)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No expense records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>