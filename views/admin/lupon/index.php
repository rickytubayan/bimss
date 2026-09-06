<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Lupon (Katarungang Pambarangay)</h1>
        <p class="page-subtitle mb-0">Mediation, pangkat & conciliation of barangay disputes</p>
    </div>
    <a href="<?= admin_url('lupon/cases/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>File New Case</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-briefcase"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Cases"><?= $stats['cases'] ?></div>
            <div class="stat-label">Total Cases</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Pending"><?= $stats['pending'] ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check2-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Settled"><?= $stats['settled'] ?></div>
            <div class="stat-label">Settled</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-patch-check"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="CFA Issued"><?= $stats['cfa'] ?></div>
            <div class="stat-label">Active Certificates</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header"><i class="bi bi-people me-2 text-primary"></i>Lupon Members (<?= count($members) ?>)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Position</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                            <?php
                            $posBadge = match ($m['position']) {
                                'chair' => 'badge-blue',
                                'secretary' => 'badge-yellow',
                                default => 'badge-gray',
                            };
                            $mBadge = $m['status'] === 'active' ? 'badge-green' : 'badge-gray';
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e($m['member_name']) ?></td>
                                <td><span class="badge rounded-pill <?= $posBadge ?>"><?= ucfirst($m['position']) ?></span></td>
                                <td><span class="badge rounded-pill <?= $mBadge ?>"><?= ucfirst($m['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No lupon members registered.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-briefcase me-2 text-primary"></i>Recent Cases</span>
                <a href="<?= admin_url('lupon/cases') ?>" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Case #</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Date Filed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentCases as $c): ?>
                            <?php
                            $stBadge = match ($c['status']) {
                                'pending_mediation' => 'badge-yellow',
                                'pending_pangkat' => 'badge-blue',
                                'pending_conciliation' => 'badge-yellow',
                                'settled', 'cfa_issued', 'executed' => 'badge-green',
                                default => 'badge-red',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold small"><?= e($c['case_number']) ?></td>
                                <td class="small"><?= e($c['complainant_name']) ?></td>
                                <td class="small"><?= e($c['respondent_last_name'] . ', ' . $c['respondent_first_name']) ?></td>
                                <td class="text-muted small"><?= format_date($c['date_filed']) ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $c['status'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentCases)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No cases filed yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>