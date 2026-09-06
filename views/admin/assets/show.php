<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title"><?= e($asset['name']) ?></h1>
        <p class="page-subtitle mb-0"><?= ucfirst($asset['category']) ?> · <?= ucfirst(str_replace('_', ' ', $asset['status'])) ?></p>
    </div>
    <a href="<?= admin_url('assets') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Assets</a>
</div>

<?php
$condBadge = match ($asset['current_condition']) {
    'excellent', 'good' => 'badge-green',
    'fair' => 'badge-yellow',
    default => 'badge-red',
};
$statusBadge = match ($asset['status']) {
    'available' => 'badge-green',
    'in_use' => 'badge-blue',
    'under_repair' => 'badge-yellow',
    default => 'badge-gray',
};
$catBadge = in_array($asset['category'], ['vehicle', 'equipment', 'facility']) ? 'badge-blue' : 'badge-yellow';
?>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-basket2"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Purchase Cost">₱<?= number_format($asset['purchase_cost'], 2) ?></div>
            <div class="stat-label">Purchase Cost</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-wrench-adjustable"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Maintenance Cost">₱<?= number_format($stats['maintenance_cost'], 2) ?></div>
            <div class="stat-label">Maintenance Cost</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-piggy-bank"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Investment">₱<?= number_format($stats['total_investment'], 2) ?></div>
            <div class="stat-label">Total Investment</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-clipboard-data"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Maintenance Records"><?= $stats['maintenance_count'] ?></div>
            <div class="stat-label">Maintenance Records</div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>Category:</strong> <span class="badge rounded-pill <?= $catBadge ?>"><?= ucfirst($asset['category']) ?></span></div>
            <div class="col-md-4"><strong>Condition:</strong> <span class="badge rounded-pill <?= $condBadge ?>"><?= ucfirst(str_replace('_', ' ', $asset['current_condition'])) ?></span></div>
            <div class="col-md-4"><strong>Status:</strong> <span class="badge rounded-pill <?= $statusBadge ?>"><?= ucfirst(str_replace('_', ' ', $asset['status'])) ?></span></div>
            <div class="col-md-4"><strong>Purchased:</strong> <?= $asset['purchase_date'] ? format_date($asset['purchase_date']) : '—' ?></div>
            <div class="col-md-4"><strong>Next Maintenance:</strong> <?= $asset['next_maintenance'] ? format_date($asset['next_maintenance']) : '—' ?></div>
            <?php if ($asset['description']): ?>
                <div class="col-12"><strong>Description:</strong> <?= e($asset['description']) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Record Maintenance</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('assets/' . $asset['id'] . '/maintenance') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="maintenance_date">Date <span class="text-danger">*</span></label>
                        <input type="date" id="maintenance_date" name="maintenance_date" class="form-control" value="<?= e(old('maintenance_date')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description <span class="text-danger">*</span></label>
                        <textarea id="description" name="description" class="form-control" rows="2" required><?= e(old('description')) ?></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="cost">Cost (₱)</label>
                            <input type="number" step="any" min="0" id="cost" name="cost" class="form-control" value="<?= e(old('cost')) ?>" placeholder="0.00">
                        </div>
                        <div class="col">
                            <label class="form-label" for="performed_by">Performed By</label>
                            <input type="text" id="performed_by" name="performed_by" class="form-control" value="<?= e(old('performed_by')) ?>" placeholder="e.g. Talyer Carlos">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="next_maintenance">Next Maintenance</label>
                        <input type="date" id="next_maintenance" name="next_maintenance" class="form-control" value="<?= e(old('next_maintenance')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Maintenance</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-wrench-adjustable me-2 text-primary"></i>Maintenance History</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="text-end">Cost</th>
                            <th>Performed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($maintenance as $m): ?>
                            <tr>
                                <td class="text-muted small"><?= format_date($m['maintenance_date']) ?></td>
                                <td class="small"><?= e($m['description']) ?></td>
                                <td class="text-end fw-semibold">₱<?= number_format($m['cost'], 2) ?></td>
                                <td class="text-muted small"><?= e($m['performed_by'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($maintenance)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No maintenance records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>