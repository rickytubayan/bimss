<?php
$c = $certificate;
$typeColors = ['residency'=>'primary','indigency'=>'success','good_moral'=>'info','jobseeker'=>'warning','birth'=>'secondary','other'=>'dark'];
$stBadge = match ($c['request_status']) {
    'pending', 'processing' => 'badge-yellow',
    'for_signing', 'ready' => 'badge-blue',
    'released' => 'badge-green',
    default => 'badge-gray',
};
$address = trim(($c['street'] ?? '') . ' ' . ($c['house_number'] ?? '')) ?: null;
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title mb-0">Certificate <?= e($c['tracking_code'] ?? ('#' . $c['id'])) ?></h1>
        <p class="page-subtitle mb-0">
            <span class="badge rounded-pill text-bg-<?= $typeColors[$c['certificate_type']] ?? 'secondary' ?>"><?= ucwords(str_replace('_', ' ', $c['certificate_type'])) ?></span>
            <span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $c['request_status'])) ?></span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if (!in_array($c['request_status'], ['released', 'cancelled'])): ?>
            <form method="POST" action="<?= admin_url('certificates/sign/' . $c['id']) ?>" class="d-inline" onsubmit="return confirm('Sign and release this certificate?');">
                <?= CSRF::field() ?>
                <button type="submit" class="btn btn-success"><i class="bi bi-pen me-1"></i>Sign &amp; Release</button>
            </form>
        <?php endif; ?>
        <a href="<?= admin_url('certificates/' . $c['id'] . '/edit') ?>" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form method="POST" action="<?= admin_url('certificates/' . $c['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this certificate?');">
            <?= CSRF::field() ?>
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
        <a href="<?= admin_url('certificates') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Certificate Details</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Tracking Code</dt>
                    <dd class="col-sm-8"><?= e($c['tracking_code'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Document Type</dt>
                    <dd class="col-sm-8"><?= e($c['type_name'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Certificate Type</dt>
                    <dd class="col-sm-8 text-capitalize"><?= e(ucwords(str_replace('_', ' ', $c['certificate_type']))) ?></dd>
                    <dt class="col-sm-4 text-muted">Purpose</dt>
                    <dd class="col-sm-8"><?= e($c['purpose'] ?: '—') ?></dd>
                    <dt class="col-sm-4 text-muted">OR Number</dt>
                    <dd class="col-sm-8"><?= e($c['or_number'] ?: '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Amount</dt>
                    <dd class="col-sm-8"><?= format_currency($c['amount']) ?></dd>
                    <dt class="col-sm-4 text-muted">Issued</dt>
                    <dd class="col-sm-8"><?= e(format_date($c['created_at'], 'M d, Y h:i A')) ?> (<?= e(time_ago($c['created_at'])) ?>)</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-person me-2 text-primary"></i>Resident</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Name</dt>
                    <dd class="col-sm-8">
                        <a href="<?= admin_url('residents/' . $c['resident_id']) ?>" class="text-decoration-none fw-semibold"><?= e($c['resident_name']) ?></a>
                    </dd>
                    <dt class="col-sm-4 text-muted">Purok / Sitio</dt>
                    <dd class="col-sm-8"><?= e($c['purok_name'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Household</dt>
                    <dd class="col-sm-8"><?= e($address ?: ('Household #' . $c['id'])) ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>