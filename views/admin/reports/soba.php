<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">SOBA Report <?= $year ?></h1>
        <p class="page-subtitle mb-0">Statement of Budget and Allocations by fund type</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <ul class="nav nav-pills nav-sm">
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports') ?>">Overview</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports/annual') ?>">Annual</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= admin_url('reports/soba') ?>">SOBA</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= admin_url('reports/budget') ?>">Budget</a></li>
        </ul>
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
            <select id="yearSelect" class="form-select" onchange="location.href = 'reports/soba?year=' + this.value">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><i class="bi bi-clipboard-data me-2 text-primary"></i>Statutory Allocations (<?= $year ?>)</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fund</th>
                    <th>Mandated %</th>
                    <th class="text-end">Allocated</th>
                    <th class="text-end">Spent</th>
                    <th class="text-end">Balance</th>
                    <th style="width: 30%;">Utilization</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allocations as $a): ?>
                    <?php $balance = (float)$a['allocated_amount'] - (float)$a['spent_amount']; ?>
                    <tr>
                        <td class="fw-semibold small"><?= ucfirst($a['fund_type']) ?></td>
                        <td><span class="badge rounded-pill badge-blue"><?= rtrim(rtrim(number_format($a['mandated_percentage'], 2), '0'), '.') ?>%</span></td>
                        <td class="text-end"><?= format_currency($a['allocated_amount']) ?></td>
                        <td class="text-end"><?= format_currency($a['spent_amount']) ?></td>
                        <td class="text-end fw-semibold"><?= format_currency($balance) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <?php $pct = (float)$a['allocated_amount'] > 0 ? round(((float)$a['spent_amount'] / (float)$a['allocated_amount']) * 100) : 0; ?>
                                    <div class="progress-bar <?= $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success') ?>" role="progressbar" style="width: <?= min($pct, 100) ?>%"></div>
                                </div>
                                <span class="small text-muted"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($allocations)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No statutory allocations recorded for <?= $year ?>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><i class="bi bi-building me-2 text-primary"></i>Approved Budgets by Fund Type (<?= $year ?>)</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fund Type</th>
                    <th>Status</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($budgets as $b): ?>
                    <?php $stBadge = match ($b['status']) { 'approved' => 'badge-green', 'active' => 'badge-blue', 'closed' => 'badge-gray', default => 'badge-yellow' }; ?>
                    <tr>
                        <td class="fw-semibold small"><?= ucfirst($b['fund_type']) ?></td>
                        <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($b['status']) ?></span></td>
                        <td class="text-end fw-semibold"><?= format_currency($b['total_amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($budgets)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">No budgets for <?= $year ?> yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>