<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Hazard Map</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($zones)) ?> mapped hazard zone<?= count($zones) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= admin_url('drrm') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to DRRM</a>
</div>

<div class="card shadow-sm">
    <div class="card-header"><i class="bi bi-map me-2 text-primary"></i>Registered Hazard Zones</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Purok</th>
                    <th>Street / Area</th>
                    <th>Hazard Type</th>
                    <th>Risk Level</th>
                    <th>Historical Events</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zones as $z): ?>
                    <?php
                    $riskBadge = match ($z['risk_level']) {
                        'very_high' => 'badge-red',
                        'high' => 'badge-red',
                        'medium' => 'badge-yellow',
                        default => 'badge-green',
                    };
                    $hzBadge = in_array($z['hazard_type'], ['flood', 'landslide', 'earthquake']) ? 'badge-blue' : 'badge-yellow';
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($z['purok_name'] ?? '—') ?></td>
                        <td class="text-muted"><?= e($z['street_name'] ?? '—') ?></td>
                        <td><span class="badge rounded-pill <?= $hzBadge ?>"><?= ucwords(str_replace('_', ' ', $z['hazard_type'])) ?></span></td>
                        <td><span class="badge rounded-pill <?= $riskBadge ?>"><?= ucwords(str_replace('_', ' ', $z['risk_level'])) ?></span></td>
                        <td class="text-center"><?= (int)$z['historical_events_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($zones)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No hazard zones mapped yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>