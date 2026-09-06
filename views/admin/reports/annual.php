<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Annual Report <?= $year ?></h1>
        <p class="page-subtitle mb-0">Yearly financial and service delivery summary</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <ul class="nav nav-pills nav-sm">
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports') ?>">Overview</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= admin_url('reports/annual') ?>">Annual</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports/soba') ?>">SOBA</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports/budget') ?>">Budget</a></li>
        </ul>
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
            <select id="yearSelect" class="form-select" onchange="location.href = 'reports/annual?year=' + this.value">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<?php if ($annualDocument): ?>
    <div class="alert alert-warning d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-text me-2"></i>A posted annual report already exists for <?= $year ?>.</span>
        <a href="<?= asset($annualDocument['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>View</a>
    </div>
<?php endif; ?>

<div class="stats-grid mb-4">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Income"><?= format_currency($income) ?></div>
            <div class="stat-label">Income</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Expenses"><?= format_currency($expense) ?></div>
            <div class="stat-label">Expenses</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon <?= $net >= 0 ? 'blue' : 'yellow' ?> d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Net"><?= format_currency($net) ?></div>
            <div class="stat-label">Net Surplus / Deficit</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-piggy-bank"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Approved Budget"><?= format_currency($approvedBudget) ?></div>
            <div class="stat-label">Approved Budget</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header"><i class="bi bi-graph-up me-2 text-primary"></i>Monthly Income vs Expense (<?= $year ?>)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Income</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $incomeMonthMap = [];
                        foreach ($monthlyIncome as $m) $incomeMonthMap[$m['m']] = $m['t'];
                        $expenseMonthMap = [];
                        foreach ($monthlyExpense as $m) $expenseMonthMap[$m['m']] = $m['t'];
                        ?>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <?php
                            $mi = $incomeMonthMap[$m] ?? 0;
                            $me = $expenseMonthMap[$m] ?? 0;
                            $mn = $mi - $me;
                            ?>
                            <tr>
                                <td class="fw-semibold small"><?= date('F', mktime(0, 0, 0, $m, 1)) ?></td>
                                <td class="text-end"><?= format_currency($mi) ?></td>
                                <td class="text-end"><?= format_currency($me) ?></td>
                                <td class="text-end fw-semibold <?= $mn >= 0 ? '' : 'text-danger' ?>"><?= format_currency($mn) ?></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th>Total</th>
                            <th class="text-end"><?= format_currency($income) ?></th>
                            <th class="text-end"><?= format_currency($expense) ?></th>
                            <th class="text-end"><?= format_currency($net) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header"><i class="bi bi-tags me-2 text-primary"></i>Income by Fund Type</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Fund Type</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        <?php foreach ($incomeByFund as $f): ?>
                            <tr><td class="fw-semibold small"><?= ucfirst($f['fund_type']) ?></td><td class="text-end"><?= format_currency($f['t']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($incomeByFund)): ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">No income recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-header border-top"><i class="bi bi-receipt me-2 text-primary"></i>Expenses by Fund Type</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Fund Type</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        <?php foreach ($expenseByFund as $f): ?>
                            <tr><td class="fw-semibold small"><?= ucfirst($f['fund_type']) ?></td><td class="text-end"><?= format_currency($f['t']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($expenseByFund)): ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">No expenses recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><i class="bi bi-people me-2 text-primary"></i>Service Delivery Summary (<?= $year ?>)</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value text-primary"><?= $serviceStats['residents'] ?></div>
                <div class="stat-label">New Residents Registered</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value text-primary"><?= $serviceStats['clearances'] ?></div>
                <div class="stat-label">Clearances Issued</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value text-primary"><?= $serviceStats['certificates'] ?></div>
                <div class="stat-label">Certificates Issued</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value text-primary"><?= format_currency($serviceStats['donations']) ?></div>
                <div class="stat-label">Donations Received</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value text-primary"><?= $serviceStats['blotters'] ?></div>
                <div class="stat-label">Blotter Reports</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value text-primary"><?= $serviceStats['complaints'] ?></div>
                <div class="stat-label">Complaints Handled</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value text-primary"><?= $serviceStats['seniors_pwd'] ?></div>
                <div class="stat-label">Senior & PWD Registered</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value text-primary"><?= format_currency($serviceStats['tax_collected']) ?></div>
                <div class="stat-label">Taxes Collected</div>
            </div>
        </div>
    </div>
</div>