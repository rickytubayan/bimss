<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Health</h1>
        <p class="page-subtitle mb-0">Maternal care, immunization, child growth & disease surveillance</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('health/maternal') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-hearts me-1"></i>Maternal</a>
        <a href="<?= admin_url('health/immunization') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-droplet-half me-1"></i>Immunization</a>
        <a href="<?= admin_url('health/growth') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-activity me-1"></i>Growth</a>
        <a href="<?= admin_url('health/surveillance') ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-eyedropper me-1"></i>Surveillance</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-person-hearts"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Pregnant Women"><?= $stats['pregnant'] ?></div>
            <div class="stat-label">Pregnant Women</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-droplet-half"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Immunizations"><?= $stats['immunization'] ?></div>
            <div class="stat-label">Immunization Records</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-activity"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Growth Records"><?= $stats['growth'] ?></div>
            <div class="stat-label">Child Growth Records</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-eyedropper"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Surveillance"><?= $stats['surveillance'] ?></div>
            <div class="stat-label">Disease Surveillance</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event me-2 text-primary"></i>Recent Health Programs</span>
                <span class="text-muted small"><?= $stats['programs'] ?> total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Target</th>
                            <th>Schedule</th>
                            <th class="text-end">Attendees</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPrograms as $p): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($p['name']) ?></td>
                                <td class="text-muted small"><?= e($p['target_group'] ?? '—') ?></td>
                                <td class="text-muted small"><?= format_date($p['schedule_date']) ?></td>
                                <td class="text-end"><?= (int)$p['attendees_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentPrograms)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No health programs yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bug me-2 text-danger"></i>Recent Disease Reports</span>
                <span class="text-muted small"><?= $stats['surveillance'] ?> total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Disease</th>
                            <th>Purok</th>
                            <th>Date</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSurveillance as $s): ?>
                            <?php
                            $stBadge = match ($s['status']) {
                                'confirmed' => 'badge-red',
                                'recovered' => 'badge-green',
                                'deceased' => 'badge-gray',
                                default => 'badge-yellow',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e(ucwords($s['disease_name'])) ?></td>
                                <td class="text-muted small"><?= e($s['purok_name'] ?? '—') ?></td>
                                <td class="text-muted small"><?= format_date($s['date_reported']) ?></td>
                                <td class="text-end"><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($s['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentSurveillance)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No disease surveillance records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>