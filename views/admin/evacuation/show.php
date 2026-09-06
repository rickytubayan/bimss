<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title"><?= e($center['name']) ?></h1>
        <p class="page-subtitle mb-0"><?= e($center['address'] ?? '') ?></p>
    </div>
    <a href="<?= admin_url('evacuation') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Evacuation</a>
</div>

<?php
$pct = $center['max_capacity'] > 0 ? round($center['current_occupancy'] / $center['max_capacity'] * 100) : 0;
$barColor = $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success');
$stBadge = match ($center['status']) {
    'open' => 'badge-green',
    'full' => 'badge-red',
    'closed' => 'badge-gray',
    default => 'badge-yellow',
};
?>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Current Occupancy"><?= $center['current_occupancy'] ?></div>
            <div class="stat-label">Current Occupancy</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-arrows-fullscreen"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Max Capacity"><?= $center['max_capacity'] ?></div>
            <div class="stat-label">Max Capacity</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-percent"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Utilization"><?= $pct ?>%</div>
            <div class="stat-label">Utilization</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-clipboard-data"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Ever Evacuated"><?= $stats['total_ever'] ?></div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>Status:</strong> <span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($center['status']) ?></span></div>
            <div class="col-md-4"><strong>Capacity:</strong> <?= $center['current_occupancy'] ?> / <?= $center['max_capacity'] ?></div>
            <div class="col-12">
                <div class="progress mb-2" style="height:8px">
                    <div class="progress-bar <?= $barColor ?>" style="width:<?= min($pct, 100) ?>%"></div>
                </div>
            </div>
            <?php if (!empty($facilities)): ?>
                <div class="col-12">
                    <strong>Facilities:</strong>
                    <?php foreach ($facilities as $f): ?>
                        <span class="badge rounded-pill badge-blue"><?= ucwords(str_replace('_', ' ', e($f))) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Check In Resident</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('evacuation/' . $center['id'] . '/checkin') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="resident_id">Resident <span class="text-danger">*</span></label>
                        <select id="resident_id" name="resident_id" class="form-select" required>
                            <option value="">Select a resident…</option>
                            <?php foreach ($residents as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Check In</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-table me-2 text-primary"></i>Occupants</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>Date In</th>
                            <th>Date Out</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($occupants as $o): ?>
                            <?php
                            $oBadge = match ($o['status']) {
                                'evacuated' => 'badge-red',
                                'returned' => 'badge-green',
                                default => 'badge-blue',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e($o['resident_name']) ?></td>
                                <td class="text-muted small"><?= format_date($o['date_in'], 'M j, Y') ?></td>
                                <td class="text-muted small"><?= $o['date_out'] ? format_date($o['date_out'], 'M j, Y') : '—' ?></td>
                                <td><span class="badge rounded-pill <?= $oBadge ?>"><?= ucfirst($o['status']) ?></span></td>
                                <td class="text-end">
                                    <?php if ($o['status'] === 'evacuated'): ?>
                                        <form method="POST" action="<?= admin_url('evacuation/' . $center['id'] . '/checkout') ?>" class="d-inline" onsubmit="return confirm('Check this resident out?')">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="occupant_id" value="<?= $o['id'] ?>">
                                            <input type="hidden" name="outcome" value="returned">
                                            <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-box-arrow-right me-1"></i>Check Out</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($occupants)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No occupants recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>