<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle mb-0">Live overview of barangay records · <?= date('F j, Y g:i A') ?></p>
    </div>
</div>

<div class="stats-grid">
    <a href="<?= admin_url('residents') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Residents"><?= number_format($stats['residents']) ?></div>
            <div class="stat-label">Active Residents</div>
        </div>
    </a>
    <a href="<?= admin_url('households') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-house"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Households"><?= number_format($stats['households']) ?></div>
            <div class="stat-label">Households</div>
        </div>
    </a>
    <a href="<?= admin_url('seniors') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-person-hearts"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Seniors and PWD"><?= number_format($stats['seniors_pwd']) ?></div>
            <div class="stat-label">Senior &amp; PWD Profiles</div>
        </div>
    </a>
    <a href="<?= admin_url('clearances') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Pending Requests"><?= number_format($stats['pending_requests']) ?></div>
            <div class="stat-label">Pending Document Requests</div>
        </div>
    </a>
    <a href="<?= admin_url('blotter') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Open Blotters"><?= number_format($stats['open_blotters']) ?></div>
            <div class="stat-label">Open Blotters</div>
        </div>
    </a>
    <a href="<?= admin_url('reports/annual') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Open Complaints"><?= number_format($stats['open_complaints']) ?></div>
            <div class="stat-label">Open Complaints</div>
        </div>
    </a>
    <a href="<?= admin_url('drrm/relief') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-box-seam"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Relief Units Available"><?= number_format($stats['relief_units']) ?></div>
            <div class="stat-label">Relief Units Available</div>
        </div>
    </a>
    <a href="<?= admin_url('finance') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-cash-coin"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Revenue This Month"><?= format_currency($stats['month_revenue']) ?></div>
            <div class="stat-label">Revenue This Month</div>
        </div>
    </a>
    <a href="<?= admin_url('bookings') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-calendar-event"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Upcoming Bookings"><?= number_format($stats['upcoming_bookings']) ?></div>
            <div class="stat-label">Upcoming Venue Bookings</div>
        </div>
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span><i class="bi bi-file-earmark-text me-2 text-primary"></i>Recent Document Requests</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Tracking</th><th>Resident</th><th>Type</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRequests as $r): ?>
                            <tr>
                                <td><span class="text-muted small"><?= e($r['tracking_code']) ?></span></td>
                                <td class="small"><?= e($r['resident_name'] ?? '—') ?></td>
                                <td class="small"><?= e($r['doc_type'] ?? '—') ?></td>
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
                                <td class="text-muted small text-nowrap"><?= format_date($r['requested_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentRequests)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No requests yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span><i class="bi bi-journal-text me-2 text-primary"></i>Recent Blotters</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Case</th><th>Incident</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBlotters as $b): ?>
                            <?php
                            $bBadge = match ($b['status']) {
                                'resolved', 'closed' => 'badge-green',
                                'investigating' => 'badge-blue',
                                'for_hearing' => 'badge-gray',
                                default => 'badge-yellow',
                            };
                            ?>
                            <tr>
                                <td class="small fw-semibold"><a href="<?= admin_url('blotter/' . (int)$b['id']) ?>" class="text-decoration-none"><?= e($b['case_number'] ?? ('#' . (int)$b['id'])) ?></a></td>
                                <td class="small text-capitalize"><?= e(str_replace('_', ' ', $b['incident_type'])) ?></td>
                                <td><span class="badge rounded-pill <?= $bBadge ?>"><?= ucwords(str_replace('_', ' ', $b['status'])) ?></span></td>
                                <td class="text-muted small text-nowrap"><?= format_date($b['date_time_of_incident'], 'M j, Y') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentBlotters)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No blotter entries yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span><i class="bi bi-arrow-up-right me-2 text-primary"></i>Recent Relief Distributions</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Date</th><th>Item</th><th class="text-end">Qty</th><th>Recipient</th><th>Event</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentDistributions as $d): ?>
                            <tr>
                                <td class="text-muted small text-nowrap"><?= format_date($d['distributed_at'], 'M j, Y g:i A') ?></td>
                                <td class="fw-semibold small"><?= e($d['item_name']) ?></td>
                                <td class="text-end"><?= (int)$d['quantity'] ?><span class="text-muted small"><?= $d['unit'] ? ' ' . e($d['unit']) : '' ?></span></td>
                                <td class="text-muted small"><?= e($d['resident_name']) ?></td>
                                <td class="text-muted small"><?= e($d['event_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentDistributions)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No relief distributions recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span><i class="bi bi-box-seam me-2 text-primary"></i>Relief Inventory Snapshot</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Item</th><th class="text-end">Qty</th><th>Status</th><th>Expiry</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reliefItems as $item): ?>
                            <?php
                            $iBadge = match ($item['status']) {
                                'available' => 'badge-green',
                                'depleted' => 'badge-red',
                                default => 'badge-gray',
                            };
                            ?>
                            <tr>
                                <td class="small fw-semibold"><?= e($item['item_name']) ?></td>
                                <td class="text-end small"><?= (int)$item['quantity'] ?><span class="text-muted"><?= $item['unit'] ? ' ' . e($item['unit']) : '' ?></span></td>
                                <td><span class="badge rounded-pill <?= $iBadge ?>"><?= ucfirst($item['status']) ?></span></td>
                                <td class="text-muted small text-nowrap"><?= $item['expiry_date'] ? format_date($item['expiry_date']) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reliefItems)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No relief items in inventory.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cash-stack me-2 text-primary"></i>Revenue by Month (<?= date('Y') ?>)</span>
                <span class="text-muted small">Total: <?= format_currency($stats['total_revenue']) ?></span>
            </div>
            <div class="card-body">
                <?php $hasRevenue = array_sum(array_column($monthlyRevenue, 'total')) > 0; ?>
                <?php if ($hasRevenue): ?>
                    <?php foreach ($monthlyRevenue as $m): ?>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-muted small" style="width: 34px;"><?= e($m['month']) ?></span>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" style="width: <?= max(0, min(100, ($m['total'] / $monthlyRevenueMax) * 100)) ?>%;" aria-valuenow="<?= (float)$m['total'] ?>" aria-valuemin="0" aria-valuemax="<?= (float)$monthlyRevenueMax ?>"></div>
                            </div>
                            <span class="small text-nowrap" style="min-width: 90px; text-align: right;"><?= format_currency($m['total']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted text-center py-4">No revenue recorded this year yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt me-2 text-primary"></i>Residents by Purok</span>
                <span class="text-muted small"><?= number_format($stats['residents']) ?> active residents</span>
            </div>
            <div class="card-body">
                <?php if (!empty($purokDistribution)): ?>
                    <?php foreach ($purokDistribution as $p): ?>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="small text-truncate" style="width: 110px;" title="<?= e($p['name']) ?>"><?= e($p['name']) ?></span>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= max(0, min(100, ((int)$p['total'] / $purokDistributionMax) * 100)) ?>%;" aria-valuenow="<?= (int)$p['total'] ?>" aria-valuemin="0" aria-valuemax="<?= (int)$purokDistributionMax ?>"></div>
                            </div>
                            <span class="small text-nowrap" style="min-width: 30px; text-align: right;"><?= (int)$p['total'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted text-center py-4">No residents recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <span><i class="bi bi-person-plus me-2 text-primary"></i>Newest Residents</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Name</th><th>Purok</th><th>Status</th><th>Registered</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentResidents as $r): ?>
                            <?php
                            $rBadge = match ($r['status']) {
                                'active' => 'badge-green',
                                'inactive' => 'badge-gray',
                                default => 'badge-yellow',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold small">
                                    <a href="<?= admin_url('residents/' . (int)$r['id']) ?>" class="text-decoration-none">
                                        <?= e(trim(($r['last_name'] ?? '') . ', ' . ($r['first_name'] ?? ''), ', ')) ?>
                                    </a>
                                </td>
                                <td class="text-muted small"><?= e($r['purok_name'] ?? '—') ?></td>
                                <td><span class="badge rounded-pill <?= $rBadge ?>"><?= ucfirst($r['status']) ?></span></td>
                                <td class="text-muted small"><?= format_date($r['created_at'], 'M j, Y') ?> <span class="fst-italic">(<?= time_ago($r['created_at']) ?>)</span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentResidents)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No residents registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
