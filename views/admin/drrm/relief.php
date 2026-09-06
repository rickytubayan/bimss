<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Relief Inventory</h1>
        <p class="page-subtitle mb-0"><?= number_format($stats['items']) ?> item<?= $stats['items'] === 1 ? '' : 's' ?> · <?= number_format($stats['available_units']) ?> units available</p>
    </div>
    <a href="<?= admin_url('drrm') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to DRRM</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-box-seam"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Item Types"><?= $stats['items'] ?></div>
            <div class="stat-label">Item Types</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Units Available"><?= number_format($stats['available_units']) ?></div>
            <div class="stat-label">Units Available</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Units Distributed"><?= number_format($stats['distributions']) ?></div>
            <div class="stat-label">Units Distributed</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Recipients"><?= number_format($stats['recipients']) ?></div>
            <div class="stat-label">Unique Recipients</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><i class="bi bi-box-seam me-2 text-primary"></i>Inventory</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i): ?>
                            <?php
                            $stBadge = match ($i['status']) {
                                'available' => 'badge-green',
                                'depleted' => 'badge-red',
                                default => 'badge-gray',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold small"><?= e($i['item_name']) ?></td>
                                <td class="text-muted small text-capitalize"><?= e($i['category']) ?></td>
                                <td class="text-end fw-semibold"><?= (int)$i['quantity'] ?></td>
                                <td class="text-muted small"><?= e($i['unit'] ?? '—') ?></td>
                                <td class="text-muted small"><?= e($i['storage_location'] ?? '—') ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($i['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No relief items in inventory.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><i class="bi bi-arrow-up-right me-2 text-primary"></i>Recent Distributions</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Recipient</th>
                            <th>Event</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($distributions as $d): ?>
                            <tr>
                                <td class="text-muted small"><?= format_date($d['distributed_at'], 'M j, Y g:i A') ?></td>
                                <td class="fw-semibold small"><?= e($d['item_name']) ?></td>
                                <td class="text-center"><?= (int)$d['quantity'] ?></td>
                                <td class="text-muted small"><?= e($d['resident_name']) ?></td>
                                <td class="text-muted small"><?= e($d['event_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($distributions)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No distributions recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>