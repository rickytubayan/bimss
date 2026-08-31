<?php
$age = $resident['birthdate'] ? date_diff(date_create($resident['birthdate']), date_create('today'))->y : null;
$displayName = trim(($resident['first_name'] ?? '') . ' ' . ($resident['middle_name'] ?? '') . ' ' . $resident['last_name'] . ($resident['suffix'] ? ' ' . $resident['suffix'] : ''));
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="avatar rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;font-size:1.4rem;background:var(--primary);color:#fff;"><?= e(strtoupper(mb_substr($resident['first_name'] ?? 'R', 0, 1))) ?></div>
        <div>
            <h1 class="page-title mb-0"><?= e($displayName) ?></h1>
            <p class="page-subtitle mb-0">
                <span class="text-capitalize"><?= e($resident['sex']) ?></span>
                <?php if ($age !== null): ?> · <?= $age ?> yrs old<?php endif; ?>
                · <?= e(ucwords($resident['national_id'] ? 'NID: ' . $resident['national_id'] : 'No National ID')) ?>
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('residents/' . $resident['id'] . '/edit') ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form method="POST" action="<?= admin_url('residents/' . $resident['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this resident?');">
            <?= CSRF::field() ?>
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
        <a href="<?= admin_url('residents') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php
$stBadge = match ($resident['status']) {
    'active' => 'badge-green',
    'inactive', 'moved_out' => 'badge-yellow',
    'deceased' => 'badge-gray',
    default => 'badge-gray',
};
?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-person me-2 text-primary"></i>Personal Information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Legal Name</dt>
                    <dd class="col-sm-8"><?= e($displayName) ?></dd>
                    <dt class="col-sm-4 text-muted">Sex</dt>
                    <dd class="col-sm-8 text-capitalize"><?= e($resident['sex']) ?></dd>
                    <dt class="col-sm-4 text-muted">Birthdate</dt>
                    <dd class="col-sm-8"><?= e(format_date($resident['birthdate'])) ?> (<?= $age !== null ? $age . ' yrs old' : '—' ?>)</dd>
                    <dt class="col-sm-4 text-muted">Civil Status</dt>
                    <dd class="col-sm-8 text-capitalize"><?= e($resident['civil_status'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Blood Type</dt>
                    <dd class="col-sm-8"><?= e($resident['blood_type'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Contact</dt>
                    <dd class="col-sm-8"><?= e($resident['phone'] ?? '—') ?><?= $resident['email'] ? ' · ' . e($resident['email']) : '' ?></dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-house me-2 text-primary"></i>Residence</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Purok / Sitio</dt>
                    <dd class="col-sm-8"><?= e($purok['name'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Household</dt>
                    <dd class="col-sm-8">
                        <?php if ($household): ?>
                            <?= e(trim(($household['street'] ?? '') . ' ' . ($household['house_number'] ?? ''))) ?: ('Household #' . $household['id']) ?>
                        <?php else: ?>—<?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-clipboard-pulse me-2 text-primary"></i>Demographics</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Disability</dt>
                    <dd class="col-sm-8 text-capitalize"><?= e($resident['disability_type'] && $resident['disability_type'] !== 'none' ? $resident['diagnosis'] ?? $resident['disability_type'] : 'None') ?></dd>
                    <dt class="col-sm-4 text-muted">Education</dt>
                    <dd class="col-sm-8 text-capitalize"><?= e(ucwords(str_replace('_', ' ', $resident['educational_attainment'] ?? 'none'))) ?></dd>
                    <dt class="col-sm-4 text-muted">Occupation</dt>
                    <dd class="col-sm-8"><?= e($resident['occupation'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Monthly Income</dt>
                    <dd class="col-sm-8"><?= $resident['monthly_income'] !== null ? format_currency($resident['monthly_income']) : '—' ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-flag me-2 text-primary"></i>Flags & Status</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Senior Citizen</span>
                        <span class="<?= $resident['is_senior'] ? 'text-success' : 'text-muted' ?>"><?= $resident['is_senior'] ? '<i class="bi bi-check-circle"></i> Yes' : '<i class="bi bi-circle"></i> No' ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>PWD</span>
                        <span class="<?= $resident['is_pwd'] ? 'text-success' : 'text-muted' ?>"><?= $resident['is_pwd'] ? '<i class="bi bi-check-circle"></i> Yes' : '<i class="bi bi-circle"></i> No' ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Registered Voter</span>
                        <span class="<?= $resident['is_voter'] ? 'text-success' : 'text-muted' ?>"><?= $resident['is_voter'] ? '<i class="bi bi-check-circle"></i> Yes' : '<i class="bi bi-circle"></i> No' ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Approved</span>
                        <span class="<?= $resident['is_approved'] ? 'text-success' : 'text-muted' ?>"><?= $resident['is_approved'] ? '<i class="bi bi-check-circle"></i> Yes' : '<i class="bi bi-circle"></i> No' ?></span>
                    </li>
                    <li class="d-flex justify-content-between pt-2">
                        <span>Status</span>
                        <span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $resident['status'])) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
