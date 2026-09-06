<?php
$sevBadge = match ($row['severity']) {
    'catastrophic' => 'badge-red',
    'severe' => 'badge-red',
    'moderate' => 'badge-yellow',
    default => 'badge-gray',
};
$typeBadge = in_array($row['type'], ['flood', 'typhoon', 'earthquake', 'volcanic']) ? 'badge-blue' : 'badge-yellow';
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title"><?= e($row['name']) ?></h1>
        <p class="page-subtitle mb-0"><?= format_date($row['datetime_start'], 'M j, Y g:i A') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('drrm/rdana/' . (int)$row['id']) ?>" class="btn btn-primary"><i class="bi bi-clipboard-data me-1"></i>RDANA</a>
        <a href="<?= admin_url('drrm/events') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge rounded-pill <?= $typeBadge ?>"><?= ucfirst($row['type']) ?></span>
            <span class="badge rounded-pill <?= $sevBadge ?>"><?= ucfirst($row['severity']) ?></span>
            <?php if ($row['datetime_end']): ?>
                <span class="badge rounded-pill badge-green">Ended <?= format_date($row['datetime_end'], 'M j, Y g:i') ?></span>
            <?php endif; ?>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small fw-semibold text-uppercase">Start</div>
                <div><?= format_date($row['datetime_start'], 'M j, Y g:i A') ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-semibold text-uppercase">Location</div>
                <div><?= $row['gps_latitude'] ? e($row['gps_latitude'] . ', ' . $row['gps_longitude']) : 'No GPS' ?></div>
            </div>
        </div>
        <?php if ($row['description']): ?>
            <hr>
            <div class="text-muted small fw-semibold text-uppercase mb-1">Description</div>
            <p class="mb-0" style="white-space: pre-line;"><?= e($row['description']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><i class="bi bi-clipboard-data me-2 text-primary"></i>RDANA Reports (<?= count($rdanas) ?>)</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Assessed By</th>
                    <th>Authority</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rdanas as $r): ?>
                    <?php $stBadge = $r['status'] === 'submitted' ? 'badge-blue' : ($r['status'] === 'consolidated' ? 'badge-green' : 'badge-gray'); ?>
                    <tr>
                        <td class="small fw-semibold"><?= format_date($r['assessment_date']) ?></td>
                        <td class="text-muted small"><?= e($r['assessed_by'] ?? '—') ?></td>
                        <td class="text-muted small"><?= e(($r['local_authority_name'] ?? '') !== '' ? $r['local_authority_name'] . ', ' . $r['local_authority_position'] : '—') ?></td>
                        <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($r['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rdanas)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No RDANA report filed yet for this event.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>