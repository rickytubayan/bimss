<?php
$address = trim(($household['street'] ?? '') . ' ' . ($household['house_number'] ?? '')) ?: ('Household #' . $household['id']);
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="avatar rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;font-size:1.4rem;background:var(--primary);color:#fff;">
            <i class="bi bi-house"></i>
        </div>
        <div>
            <h1 class="page-title mb-0"><?= e($address) ?></h1>
            <p class="page-subtitle mb-0">
                ID: #<?= $household['id'] ?>
                · <?= e(ucwords($household['classification'])) ?>
                <?php if ($purok): ?> · <?= e($purok['name']) ?><?php endif; ?>
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('households/' . $household['id'] . '/edit') ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form method="POST" action="<?= admin_url('households/' . $household['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this household?');">
            <?= CSRF::field() ?>
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
        <a href="<?= admin_url('households') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php
$stBadge = match ($household['status']) {
    'active' => 'badge-green',
    'vacant' => 'badge-yellow',
    'demolished' => 'badge-gray',
    default => 'badge-gray',
};
?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-house me-2 text-primary"></i>Household Details</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Address</dt>
                    <dd class="col-sm-8"><?= e($address) ?></dd>
                    <dt class="col-sm-4 text-muted">Purok / Sitio</dt>
                    <dd class="col-sm-8"><?= e($purok['name'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Classification</dt>
                    <dd class="col-sm-8 text-capitalize"><?= e($household['classification']) ?></dd>
                    <dt class="col-sm-4 text-muted">Status</dt>
                    <dd class="col-sm-8"><span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords($household['status']) ?></span></dd>
                    <dt class="col-sm-4 text-muted">Created</dt>
                    <dd class="col-sm-8"><?= e(format_date($household['created_at'], 'M d, Y h:i A')) ?></dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-people me-2 text-primary"></i>Members (<?= count($members) ?>)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Sex</th>
                            <th>Age</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                            <?php
                                $age = $m['birthdate'] ? date_diff(date_create($m['birthdate']), date_create('today'))->y : null;
                                $name = trim(($m['first_name'] ?? '') . ' ' . ($m['middle_name'] ?? '') . ' ' . $m['last_name'] . ($m['suffix'] ? ' ' . $m['suffix'] : ''));
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= admin_url('residents/' . $m['id']) ?>" class="text-decoration-none fw-semibold">
                                        <?= e($name) ?>
                                    </a>
                                    <?php if ($head && $head['id'] === $m['id']): ?>
                                        <span class="badge rounded-pill badge-blue ms-1" title="Household Head">Head</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-capitalize"><?= e($m['sex']) ?></td>
                                <td><?= $age !== null ? $age : '—' ?></td>
                                <td>
                                    <?php
                                    $mBadge = match ($m['status']) {
                                        'active' => 'badge-green',
                                        'inactive', 'moved_out' => 'badge-yellow',
                                        'deceased' => 'badge-gray',
                                        default => 'badge-gray',
                                    };
                                    ?>
                                    <span class="badge rounded-pill <?= $mBadge ?>"><?= ucwords(str_replace('_', ' ', $m['status'])) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No members in this household.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-person me-2 text-primary"></i>Household Head</div>
            <div class="card-body">
                <?php if ($head): ?>
                    <?php $headName = trim(($head['first_name'] ?? '') . ' ' . ($head['last_name'] ?? '')); ?>
                    <a href="<?= admin_url('residents/' . $head['id']) ?>" class="fw-semibold text-decoration-none d-block mb-2"><?= e($headName) ?></a>
                    <span class="text-muted small"><?= e($head['phone'] ?? 'No phone') ?></span>
                <?php else: ?>
                    <span class="text-muted">No head assigned.</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($pets)): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-heart me-2 text-primary"></i>Pets (<?= count($pets) ?>)</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($pets as $pet): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= e($pet['name'] ?? $pet['species']) ?></span>
                        <span class="text-muted text-capitalize"><?= e($pet['species']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($vehicles)): ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-car-front me-2 text-primary"></i>Vehicles (<?= count($vehicles) ?>)</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($vehicles as $v): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= e($v['make_model'] ?? $v['type']) ?></span>
                        <span class="text-muted"><?= e($v['plate_no'] ?? '—') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
