<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Lupon Cases</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($rows)) ?> case<?= count($rows) === 1 ? '' : 's' ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('lupon/cases/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>File New Case</a>
        <a href="<?= admin_url('lupon') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-briefcase me-2 text-primary"></i>All Cases</span>
        <ul class="nav nav-pills nav-sm">
            <li class="nav-item"><a class="nav-link py-1 px-2 <?= $status === '' ? 'active' : '' ?>" href="<?= admin_url('lupon/cases') ?>">All</a></li>
            <?php foreach (['pending_mediation', 'pending_pangkat', 'pending_conciliation', 'settled', 'repudiated', 'cfa_issued', 'barred', 'executed'] as $st): ?>
                <li class="nav-item">
                    <a class="nav-link py-1 px-2 <?= $status === $st ? 'active' : '' ?>" href="<?= admin_url('lupon/cases?status=' . $st) ?>"><?= ucwords(str_replace('_', ' ', $st)) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Case #</th>
                    <th>Complainant</th>
                    <th>Respondent</th>
                    <th>Nature</th>
                    <th>Date Filed</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $c): ?>
                    <?php
                    $stBadge = match ($c['status']) {
                        'pending_mediation' => 'badge-yellow',
                        'pending_pangkat' => 'badge-blue',
                        'pending_conciliation' => 'badge-yellow',
                        'settled', 'cfa_issued', 'executed' => 'badge-green',
                        default => 'badge-red',
                    };
                    $natureBadge = $c['nature_of_dispute'] === 'criminal' ? 'badge-red' : ($c['nature_of_dispute'] === 'civil' ? 'badge-blue' : 'badge-gray');
                    ?>
                    <tr>
                        <td class="fw-semibold small"><?= e($c['case_number']) ?></td>
                        <td class="small"><?= e($c['complainant_name']) ?></td>
                        <td class="small"><?= e($c['respondent_name']) ?></td>
                        <td><span class="badge rounded-pill <?= $natureBadge ?>"><?= ucfirst($c['nature_of_dispute']) ?></span></td>
                        <td class="text-muted small"><?= format_date($c['date_filed']) ?></td>
                        <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $c['status'])) ?></span></td>
                        <td class="text-end">
                            <a href="<?= admin_url('lupon/cases/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No cases filed yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>