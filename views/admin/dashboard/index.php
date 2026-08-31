<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Residents"><?= number_format($stats['residents']) ?></div>
            <div class="stat-label">Residents</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-house"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Households"><?= number_format($stats['households']) ?></div>
            <div class="stat-label">Households</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Pending Requests"><?= number_format($stats['pending_requests']) ?></div>
            <div class="stat-label">Pending Document Requests</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Open Complaints"><?= number_format($stats['open_complaints']) ?></div>
            <div class="stat-label">Open Complaints</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Open Blotters"><?= number_format($stats['open_blotters']) ?></div>
            <div class="stat-label">Open Blotters</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-cash-coin"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Revenue"><?= format_currency($stats['total_revenue']) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text me-2 text-primary"></i>Recent Document Requests</span>
                <a href="<?= admin_url('clearances') ?>" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Tracking</th><th>Type</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRequests as $r): ?>
                            <tr>
                                <td><span class="text-muted small"><?= e($r['tracking_code']) ?></span></td>
                                <td><?= e($r['doc_type'] ?? '—') ?></td>
                                <td>
                                    <?php
                                    $badge = match ($r['status']) {
                                        'pending' => 'badge-yellow',
                                        'processing', 'for_signing' => 'badge-blue',
                                        'ready' => 'badge-green',
                                        'released' => 'badge-gray',
                                        default => 'badge-gray',
                                    };
                                    ?>
                                    <span class="badge rounded-pill <?= $badge ?>"><?= ucwords(str_replace('_', ' ', $r['status'])) ?></span>
                                </td>
                                <td class="text-muted small"><?= format_date($r['requested_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentRequests)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No requests yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex align-items-center"><i class="bi bi-exclamation-circle me-2 text-danger"></i>Recent Complaints</div>
            <div class="list-group list-group-flush">
                <?php foreach ($recentComplaints as $c): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="small"><?= e(ucwords(str_replace('_', ' ', $c['category']))) ?></strong>
                            <span class="badge rounded-pill badge-yellow"><?= ucwords($c['status']) ?></span>
                        </div>
                        <div class="text-muted small"><?= truncate($c['description'], 90) ?></div>
                        <div class="text-muted small fst-italic"><?= time_ago($c['created_at']) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($recentComplaints)): ?>
                    <div class="list-group-item text-muted text-center py-4">No complaints yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <a href="<?= admin_url('residents/create') ?>" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>New Resident</a>
    <a href="<?= admin_url('reports') ?>" class="btn btn-outline-primary"><i class="bi bi-graph-up me-1"></i>View Reports</a>
</div>
