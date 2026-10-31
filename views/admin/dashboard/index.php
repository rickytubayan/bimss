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
            <div class="stat-value" data-read-aloud="Active Residents"><?= number_format($stats['residents']) ?></div>
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
    <a href="<?= admin_url('clearances') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Pending Document Requests"><?= number_format($stats['pending_requests']) ?></div>
            <div class="stat-label">Pending Document Requests</div>
        </div>
    </a>
    <a href="<?= admin_url('appointments') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-calendar-check"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Appointments Today"><?= number_format($stats['appointments_today']) ?></div>
            <div class="stat-label">Appointments Today</div>
        </div>
    </a>
    <a href="<?= admin_url('finance') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-cash-coin"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Revenue This Month"><?= format_currency($stats['revenue_month']) ?></div>
            <div class="stat-label">Revenue This Month</div>
        </div>
    </a>
    <a href="<?= admin_url('finance/expenses') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Expenses This Month"><?= format_currency($stats['expenses_month']) ?></div>
            <div class="stat-label">Expenses This Month</div>
        </div>
    </a>
    <a href="<?= admin_url('seniors') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-person-hearts"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Senior and PWD Profiles"><?= number_format($stats['seniors_pwd']) ?></div>
            <div class="stat-label">Senior &amp; PWD Profiles</div>
        </div>
    </a>
    <a href="<?= admin_url('health') ?>" class="stat-card d-flex align-items-center gap-3 text-decoration-none">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-heart-pulse"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Pregnant Women"><?= number_format($stats['pregnant']) ?></div>
            <div class="stat-label">Pregnant Women</div>
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
                            <tr><td colspan="5" class="text-center text-muted py-4">No document requests yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span><i class="bi bi-heart-pulse me-2 text-primary"></i>Health &amp; Welfare Snapshot</span>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small"><i class="bi bi-gender-female me-2 text-muted"></i>Maternal Records</span>
                    <span class="badge rounded-pill badge-blue"><?= number_format($health['maternal']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small"><i class="bi bi-heart me-2 text-muted"></i>Pregnant Women</span>
                    <span class="badge rounded-pill badge-yellow"><?= number_format($health['pregnant']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small"><i class="bi bi-shield-plus me-2 text-muted"></i>Immunization Records</span>
                    <span class="badge rounded-pill badge-green"><?= number_format($health['immunization']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small"><i class="bi bi-activity me-2 text-muted"></i>Child Growth Records</span>
                    <span class="badge rounded-pill badge-green"><?= number_format($health['growth']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small"><i class="bi bi-bug me-2 text-muted"></i>Disease Surveillance</span>
                    <span class="badge rounded-pill badge-gray"><?= number_format($health['surveillance']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small"><i class="bi bi-exclamation-triangle me-2 text-muted"></i>Active Cases</span>
                    <span class="badge rounded-pill <?= $health['active_surveillance'] > 0 ? 'badge-red' : 'badge-gray' ?>"><?= number_format($health['active_surveillance']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small"><i class="bi bi-calendar-heart me-2 text-muted"></i>Health Programs</span>
                    <span class="badge rounded-pill badge-blue"><?= number_format($health['programs']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span><i class="bi bi-bar-chart-line me-2 text-primary"></i>Revenue vs Expenses (<?= date('Y') ?>)</span>
                <span class="text-muted small">
                    Revenue: <?= format_currency($stats['total_revenue']) ?>
                    · Expenses: <?= format_currency($stats['total_expenses']) ?>
                </span>
            </div>
            <div class="card-body">
                <?php $hasFinance = array_sum(array_column($monthlyFinance, 'income')) > 0 || array_sum(array_column($monthlyFinance, 'expense')) > 0; ?>
                <?php if ($hasFinance): ?>
                    <?php foreach ($monthlyFinance as $m): ?>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-muted small" style="width: 34px;"><?= e($m['month']) ?></span>
                            <div class="flex-grow-1">
                                <div class="progress mb-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= max(0, min(100, ($m['income'] / $financeMax) * 100)) ?>%;" aria-valuenow="<?= (float)$m['income'] ?>" aria-valuemin="0" aria-valuemax="<?= (float)$financeMax ?>" title="Revenue"></div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= max(0, min(100, ($m['expense'] / $financeMax) * 100)) ?>%;" aria-valuenow="<?= (float)$m['expense'] ?>" aria-valuemin="0" aria-valuemax="<?= (float)$financeMax ?>" title="Expenses"></div>
                                </div>
                            </div>
                            <span class="small text-nowrap text-end" style="min-width: 170px;">
                                <span class="text-success"><?= format_currency($m['income']) ?></span>
                                <span class="text-muted">/</span>
                                <span class="text-danger"><?= format_currency($m['expense']) ?></span>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
                        <span><span class="badge rounded-pill badge-green">Revenue</span></span>
                        <span><span class="badge rounded-pill badge-red">Expenses</span></span>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">No revenue or expenses recorded this year yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span><i class="bi bi-calendar-heart me-2 text-primary"></i>Recent Health Programs</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Program</th><th>Target</th><th class="text-end">Attendees</th><th>Schedule</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentHealthPrograms as $p): ?>
                            <tr>
                                <td class="small fw-semibold"><?= e($p['name']) ?></td>
                                <td class="small text-muted"><?= e($p['target_group'] ?: '—') ?></td>
                                <td class="text-end small"><?= number_format((int)$p['attendees_count']) ?></td>
                                <td class="text-muted small text-nowrap"><?= format_date($p['schedule_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentHealthPrograms)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No health programs recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
