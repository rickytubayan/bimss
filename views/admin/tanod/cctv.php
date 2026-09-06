<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">CCTV Cameras</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($cameras)) ?> camera<?= count($cameras) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= admin_url('tanod') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Tanod</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-camera-video"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Cameras"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Cameras</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-camera-video-fill"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Online"><?= $stats['online'] ?></div>
            <div class="stat-label">Online</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-camera-video-off"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Offline"><?= $stats['offline'] ?></div>
            <div class="stat-label">Offline</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-geo-alt"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Monitored"><?= count(array_filter($cameras, fn($c) => $c['gps_latitude'])) ?></div>
            <div class="stat-label">GPS Tagged</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><i class="bi bi-camera-video me-2 text-primary"></i>Camera Registry</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>IP Address</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cameras as $c): ?>
                    <?php
                    $stBadge = match ($c['status']) {
                        'online' => 'badge-green',
                        'maintenance' => 'badge-yellow',
                        default => 'badge-red',
                    };
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($c['name']) ?></td>
                        <td class="text-muted"><?= e($c['location']) ?></td>
                        <td class="text-muted small"><?= e($c['ip_address'] ?? '—') ?></td>
                        <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($c['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($cameras)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No CCTV cameras registered.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>