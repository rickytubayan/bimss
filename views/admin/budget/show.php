<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title"><?= (int)$budget['fiscal_year'] ?> <?= strtoupper($budget['fund_type']) ?> Budget</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($lineItems)) ?> line item(s)</p>
    </div>
    <a href="<?= admin_url('budget') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Budget</a>
</div>

<?php
$stBadge = match ($budget['status']) {
    'approved', 'active' => 'badge-green',
    'draft' => 'badge-yellow',
    'closed' => 'badge-gray',
    default => 'badge-gray',
};
?>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-clipboard-data"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Budget"><?= format_currency($budget['total_amount']) ?></div>
            <div class="stat-label">Total Budget</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-diagram-3"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Allocated"><?= format_currency($totalAllocated) ?></div>
            <div class="stat-label">Allocated</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-box-arrow-up"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Utilized"><?= format_currency($totalUtilized) ?></div>
            <div class="stat-label">Utilized</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Remaining Balance"><?= format_currency($totalAllocated - $totalUtilized) ?></div>
            <div class="stat-label">Remaining Balance</div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center gap-3 mb-3">
    <span class="badge rounded-pill <?= $stBadge ?> fs-6"><?= ucwords($budget['status']) ?></span>
    <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>Created <?= format_date($budget['created_at']) ?></span>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center"><i class="bi bi-list-ul me-2 text-primary"></i>Line Items</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Account</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="text-end">Allocated</th>
                    <th class="text-end">Utilized</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lineItems as $li): ?>
                    <tr>
                        <td class="text-muted"><?= e($li['code']) ?></td>
                        <td class="fw-semibold"><?= e($li['name']) ?></td>
                        <td><span class="text-capitalize small"><?= e($li['type']) ?></span></td>
                        <td class="text-muted"><?= e($li['description'] ?? '—') ?></td>
                        <td class="text-end"><?= format_currency($li['allocated_amount']) ?></td>
                        <td class="text-end text-danger"><?= format_currency($li['utilized_amount']) ?></td>
                        <td class="text-end text-success fw-semibold"><?= format_currency((float)$li['allocated_amount'] - (float)$li['utilized_amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($lineItems)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No line items.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>