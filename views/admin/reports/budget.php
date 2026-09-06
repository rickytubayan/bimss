<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Budget Report <?= $year ?></h1>
        <p class="page-subtitle mb-0">Budget allocations, utilization, and balances for fiscal year <?= $year ?></p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <ul class="nav nav-pills nav-sm">
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports') ?>">Overview</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports/annual') ?>">Annual</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports/soba') ?>">SOBA</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= admin_url('reports/budget') ?>">Budget</a></li>
        </ul>
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
            <select id="yearSelect" class="form-select" onchange="location.href = 'reports/budget?year=' + this.value">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-wallet2"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Allocated"><?= format_currency($totals['allocated']) ?></div>
            <div class="stat-label">Total Allocated</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-arrow-right-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Utilized"><?= format_currency($totals['utilized']) ?></div>
            <div class="stat-label">Total Utilized</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-piggy-bank"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Balance"><?= format_currency($totals['balance']) ?></div>
            <div class="stat-label">Remaining Balance</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon <?= $totals['allocated'] > 0 && ($totals['utilized'] / $totals['allocated']) > 0.75 ? 'yellow' : 'blue' ?> d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-percent"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Utilization Rate"><?= $totals['allocated'] > 0 ? round(($totals['utilized'] / $totals['allocated']) * 100) : 0 ?>%</div>
            <div class="stat-label">Utilization Rate</div>
        </div>
    </div>
</div>

<?php foreach ($budgets as $b): ?>
    <?php
    $stBadge = match ($b['status']) { 'approved' => 'badge-green', 'active' => 'badge-blue', 'closed' => 'badge-gray', default => 'badge-yellow' };
    $alloc = 0.0; $util = 0.0;
    foreach ($b['items'] as $it) { $alloc += (float)$it['allocated_amount']; $util += (float)$it['utilized_amount']; }
    $pct = $alloc > 0 ? round(($util / $alloc) * 100) : 0;
    ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="bi bi-building me-2 text-primary"></i><?= ucfirst($b['fund_type']) ?> Fund <span class="badge rounded-pill <?= $stBadge ?> ms-2"><?= ucfirst($b['status']) ?></span></span>
            <span class="small text-muted">Budget Amount: <strong><?= format_currency($b['total_amount']) ?></strong></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 35%;">Account</th>
                        <th class="text-end">Allocated</th>
                        <th class="text-end">Utilized</th>
                        <th class="text-end">Balance</th>
                        <th style="width: 25%;">Utilization</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($b['items'] as $it): ?>
                        <?php $itemPct = (float)$it['allocated_amount'] > 0 ? round(((float)$it['utilized_amount'] / (float)$it['allocated_amount']) * 100) : 0; ?>
                        <tr>
                            <td class="small" style="width: 35%;">
                                <span class="fw-semibold"><?= e($it['account_name'] ?: '—') ?></span>
                                <?php if (!empty($it['item_desc'])): ?><div class="text-muted"><?= e($it['item_desc']) ?></div><?php endif; ?>
                            </td>
                            <td class="text-end small"><?= format_currency($it['allocated_amount']) ?></td>
                            <td class="text-end small"><?= format_currency($it['utilized_amount']) ?></td>
                            <td class="text-end small fw-semibold"><?= format_currency((float)$it['allocated_amount'] - (float)$it['utilized_amount']) ?></td>
                            <td style="width: 25%;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar <?= $itemPct >= 90 ? 'bg-danger' : ($itemPct >= 60 ? 'bg-warning' : 'bg-success') ?>" role="progressbar" style="width: <?= min($itemPct, 100) ?>%"></div>
                                    </div>
                                    <span class="small text-muted"><?= $itemPct ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($b['items'])): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">No line items for this budget.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th>Total</th>
                        <th class="text-end"><?= format_currency($alloc) ?></th>
                        <th class="text-end"><?= format_currency($util) ?></th>
                        <th class="text-end"><?= format_currency($alloc - $util) ?></th>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= min($pct, 100) ?>%"></div>
                                </div>
                                <span class="small"><?= $pct ?>%</span>
                            </div>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endforeach; ?>

<?php if (empty($budgets)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center text-muted py-5">No budgets for <?= $year ?> yet.</div>
    </div>
<?php endif; ?>