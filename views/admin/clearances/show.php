<?php
$displayName = trim(($clearance['first_name'] ?? '') . ' ' . ($clearance['middle_name'] ?? '') . ' ' . $clearance['last_name'] . ($clearance['suffix'] ? ' ' . $clearance['suffix'] : ''));
$age = $clearance['birthdate'] ? date_diff(date_create($clearance['birthdate']), date_create('today'))->y : null;

$statusBadge = match ($clearance['status']) {
    'pending' => 'badge-yellow',
    'processing' => 'badge-blue',
    'for_signing' => 'badge-blue',
    'ready' => 'badge-green',
    'released' => 'badge-gray',
    'cancelled' => 'badge-red',
    default => 'badge-gray',
};
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="avatar rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;font-size:1.4rem;background:var(--primary);color:#fff;">
            <i class="bi bi-file-earmark-text"></i>
        </div>
        <div>
            <h1 class="page-title mb-0">Clearance #<?= e($clearance['tracking_code']) ?></h1>
            <p class="page-subtitle mb-0">
                <span class="badge rounded-pill <?= $statusBadge ?>"><?= ucwords(str_replace('_', ' ', $clearance['status'])) ?></span>
            </p>
        </div>
    </div>
    <a href="<?= admin_url('clearances') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-file-earmark me-2 text-primary"></i>Clearance Information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Tracking Code</dt>
                    <dd class="col-sm-8"><code><?= e($clearance['tracking_code']) ?></code></dd>
                    <dt class="col-sm-4 text-muted">Document Type</dt>
                    <dd class="col-sm-8"><?= e($clearance['document_type_name']) ?></dd>
                    <dt class="col-sm-4 text-muted">Fee</dt>
                    <dd class="col-sm-8"><?= format_currency($clearance['fee']) ?></dd>
                    <dt class="col-sm-4 text-muted">Purpose</dt>
                    <dd class="col-sm-8"><?= e($clearance['purpose'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Status</dt>
                    <dd class="col-sm-8"><span class="badge rounded-pill <?= $statusBadge ?>"><?= ucwords(str_replace('_', ' ', $clearance['status'])) ?></span></dd>
                    <dt class="col-sm-4 text-muted">Requested At</dt>
                    <dd class="col-sm-8"><?= format_date($clearance['requested_at'], 'M d, Y h:i A') ?></dd>
                    <?php if ($clearance['processed_at']): ?>
                        <dt class="col-sm-4 text-muted">Processed At</dt>
                        <dd class="col-sm-8"><?= format_date($clearance['processed_at'], 'M d, Y h:i A') ?></dd>
                    <?php endif; ?>
                    <?php if ($clearance['released_at']): ?>
                        <dt class="col-sm-4 text-muted">Released At</dt>
                        <dd class="col-sm-8"><?= format_date($clearance['released_at'], 'M d, Y h:i A') ?></dd>
                    <?php endif; ?>
                    <?php if ($clearance['released_to']): ?>
                        <dt class="col-sm-4 text-muted">Released To</dt>
                        <dd class="col-sm-8"><?= e($clearance['released_to']) ?></dd>
                    <?php endif; ?>
                    <?php if ($clearance['representative_name']): ?>
                        <dt class="col-sm-4 text-muted">Representative</dt>
                        <dd class="col-sm-8"><?= e($clearance['representative_name']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-person me-2 text-primary"></i>Resident Information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Full Name</dt>
                    <dd class="col-sm-8">
                        <a href="<?= admin_url('residents/' . $clearance['resident_id']) ?>" class="fw-semibold text-decoration-none">
                            <?= e($displayName) ?>
                        </a>
                    </dd>
                    <dt class="col-sm-4 text-muted">Sex</dt>
                    <dd class="col-sm-8 text-capitalize"><?= e($clearance['sex']) ?></dd>
                    <dt class="col-sm-4 text-muted">Age</dt>
                    <dd class="col-sm-8"><?= $age !== null ? $age . ' years old' : '—' ?></dd>
                    <dt class="col-sm-4 text-muted">Phone</dt>
                    <dd class="col-sm-8"><?= e($clearance['resident_phone'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Email</dt>
                    <dd class="col-sm-8"><?= e($clearance['resident_email'] ?? '—') ?></dd>
                </dl>
            </div>
        </div>

        <?php if ($clearance['document_description']): ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-info-circle me-2 text-primary"></i>Description</div>
            <div class="card-body">
                <p class="mb-0"><?= e($clearance['document_description']) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-gear me-2 text-primary"></i>Workflow Actions</div>
            <div class="card-body">
                <?php if ($clearance['status'] === 'pending'): ?>
                    <p class="text-muted mb-3">This clearance is awaiting processing.</p>
                    <form method="POST" action="<?= admin_url('clearances/process/' . $clearance['id']) ?>" onsubmit="return confirm('Start processing this clearance?');">
                        <?= CSRF::field() ?>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-play-circle me-1"></i>Start Processing</button>
                    </form>
                <?php elseif ($clearance['status'] === 'for_signing'): ?>
                    <p class="text-muted mb-3">This clearance is ready for signing.</p>
                    <form method="POST" action="<?= admin_url('clearances/sign/' . $clearance['id']) ?>" onsubmit="return confirm('Sign this clearance?');">
                        <?= CSRF::field() ?>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-pen me-1"></i>Sign Clearance</button>
                    </form>
                <?php elseif ($clearance['status'] === 'ready'): ?>
                    <p class="text-muted mb-3">This clearance is signed and ready for release.</p>
                    <form method="POST" action="<?= admin_url('clearances/release/' . $clearance['id']) ?>" onsubmit="return confirm('Release this clearance?');">
                        <?= CSRF::field() ?>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i>Release Clearance</button>
                    </form>
                <?php elseif ($clearance['status'] === 'processing'): ?>
                    <p class="text-muted mb-0">This clearance is currently being processed. No actions available yet.</p>
                <?php elseif ($clearance['status'] === 'released'): ?>
                    <p class="text-muted mb-0">This clearance has been released. Workflow complete.</p>
                <?php elseif ($clearance['status'] === 'cancelled'): ?>
                    <p class="text-muted mb-0">This clearance has been cancelled.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($clearance['assisted_by']): ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-person-check me-2 text-primary"></i>Assisted By</div>
            <div class="card-body">
                <p class="mb-0"><?= e(trim(($clearance['assist_first_name'] ?? '') . ' ' . ($clearance['assist_last_name'] ?? ''))) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
