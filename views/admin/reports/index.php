<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Reports</h1>
        <p class="page-subtitle mb-0">Financial and operational summaries of the barangay</p>
    </div>
    <div class="col-auto">
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
            <select id="yearSelect" class="form-select" onchange="location.href = '?year=' + this.value">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Income"><?= format_currency($stats['income']) ?></div>
            <div class="stat-label">Income (<?= $year ?>)</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Expenses"><?= format_currency($stats['expense']) ?></div>
            <div class="stat-label">Expenses (<?= $year ?>)</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon <?= $stats['net'] >= 0 ? 'blue' : 'yellow' ?> d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Net"><?= format_currency($stats['net']) ?></div>
            <div class="stat-label">Net Surplus / Deficit</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-piggy-bank"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Approved Budget"><?= format_currency($stats['budget']) ?></div>
            <div class="stat-label">Approved Budget (<?= $year ?>)</div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header"><i class="bi bi-menu-button-wide me-2 text-primary"></i>Available Reports</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <a href="<?= admin_url('reports/annual?year=' . $year) ?>" class="text-decoration-none">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="stat-icon blue d-flex align-items-center justify-content-center"><i class="bi bi-calendar2-check"></i></div>
                            <h6 class="mb-0 fw-semibold">Annual Report</h6>
                        </div>
                        <p class="text-muted small mb-0">Yearly summary of income vs expenses, fund utilization, and barangay service delivery statistics.</p>
                        <span class="btn btn-sm btn-outline-primary mt-2">Open</span>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= admin_url('reports/soba?year=' . $year) ?>" class="text-decoration-none">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="stat-icon yellow d-flex align-items-center justify-content-center"><i class="bi bi-clipboard-data"></i></div>
                            <h6 class="mb-0 fw-semibold">SOBA Report</h6>
                        </div>
                        <p class="text-muted small mb-0">Statement of Budget and Allocations: statutory fund allocations and spending per fund type.</p>
                        <span class="btn btn-sm btn-outline-primary mt-2">Open</span>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= admin_url('reports/budget?year=' . $year) ?>" class="text-decoration-none">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="stat-icon green d-flex align-items-center justify-content-center"><i class="bi bi-bar-chart"></i></div>
                            <h6 class="mb-0 fw-semibold">Budget Report</h6>
                        </div>
                        <p class="text-muted small mb-0">Budget utilization per fund type with line-item allocations, actual spending, and balances.</p>
                        <span class="btn btn-sm btn-outline-primary mt-2">Open</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-pie-chart me-2 text-primary"></i>Expenses by Fund Type (<?= $year ?>)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fund Type</th>
                    <th>Transactions</th>
                    <th class="text-end">Total Amount</th>
                    <th style="width: 40%;">Share</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expensesByFund as $e): ?>
                    <tr>
                        <td class="fw-semibold small"><?= ucfirst($e['fund_type']) ?></td>
                        <td><span class="badge rounded-pill badge-blue"><?= $e['c'] ?></span></td>
                        <td class="text-end fw-semibold"><?= format_currency($e['t']) ?></td>
                        <td>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $stats['expense'] > 0 ? round(($e['t'] / $stats['expense']) * 100) : 0 ?>%"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($expensesByFund)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No expense records for <?= $year ?> yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>