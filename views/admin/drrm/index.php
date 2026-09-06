<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Disaster Risk Reduction & Management</h1>
        <p class="page-subtitle mb-0">Events, RDANA assessment, hazard zones & relief operations</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('drrm/events') ?>" class="btn btn-outline-primary"><i class="bi bi-lightning-charge me-1"></i>Events</a>
        <a href="<?= admin_url('drrm/hazard-map') ?>" class="btn btn-outline-secondary"><i class="bi bi-map me-1"></i>Hazard Map</a>
        <a href="<?= admin_url('drrm/relief') ?>" class="btn btn-outline-secondary"><i class="bi bi-box-seam me-1"></i>Relief</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-lightning-charge"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Disaster Events"><?= $stats['events'] ?></div>
            <div class="stat-label">Disaster Events</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-exclamation-octagon"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Severe"><?= $stats['severe'] ?></div>
            <div class="stat-label">Severe / Catastrophic</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-clipboard-data"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="RDANA Reports"><?= $stats['rdana'] ?></div>
            <div class="stat-label">RDANA Reports</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-box-seam"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Relief Units"><?= number_format($stats['relief']) ?></div>
            <div class="stat-label">Relief Units Available</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-lightning-charge me-2 text-primary"></i>Recent Disaster Events</span>
        <a href="<?= admin_url('drrm/events') ?>" class="btn btn-sm btn-outline-primary">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Type</th>
                    <th>Started</th>
                    <th>Severity</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentEvents as $e): ?>
                    <?php
                    $sevBadge = match ($e['severity']) {
                        'catastrophic' => 'badge-red',
                        'severe' => 'badge-red',
                        'moderate' => 'badge-yellow',
                        default => 'badge-gray',
                    };
                    $typeBadge = in_array($e['type'], ['flood', 'typhoon', 'earthquake', 'volcanic']) ? 'badge-blue' : 'badge-yellow';
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($e['name']) ?></td>
                        <td><span class="badge rounded-pill <?= $typeBadge ?>"><?= ucfirst($e['type']) ?></span></td>
                        <td class="text-muted small"><?= format_date($e['datetime_start'], 'M j, Y g:i A') ?></td>
                        <td><span class="badge rounded-pill <?= $sevBadge ?>"><?= ucfirst($e['severity']) ?></span></td>
                        <td class="text-end">
                            <a href="<?= admin_url('drrm/events/' . $e['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentEvents)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No disaster events recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>