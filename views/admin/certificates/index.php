<?php
$certTypes = ['residency','indigency','good_moral','jobseeker','birth','other'];
$typeColors = ['residency'=>'primary','indigency'=>'success','good_moral'=>'info','jobseeker'=>'warning','birth'=>'secondary','other'=>'dark'];
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Certificates</h1>
        <p class="page-subtitle mb-0"><?= number_format($total) ?> certificate(s)</p>
    </div>
    <a href="<?= admin_url('certificates/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Certificate</a>
</div>

<form method="GET" action="<?= admin_url('certificates') ?>" class="row g-2 mb-3">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Search resident, type, purpose..." value="<?= e($search) ?>">
        </div>
    </div>
    <div class="col-md-3">
        <select name="type" class="form-select">
            <option value="">All types</option>
            <?php foreach ($certTypes as $ct): ?>
                <option value="<?= $ct ?>" <?= $type === $ct ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $ct)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>Resident</th><th>Type</th><th>Purpose</th><th>OR No.</th><th>Amount</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($certificates as $c): ?>
                    <tr>
                        <td>
                            <a href="<?= admin_url('residents/' . $c['resident_id']) ?>" class="text-decoration-none fw-semibold"><?= e($c['resident_name']) ?></a>
                        </td>
                        <td><span class="badge rounded-pill text-bg-<?= $typeColors[$c['certificate_type']] ?? 'secondary' ?>"><?= ucwords(str_replace('_', ' ', $c['certificate_type'])) ?></span></td>
                        <td class="text-muted"><?= e(truncate($c['purpose'] ?? '', 50)) ?></td>
                        <td class="text-muted small"><?= e($c['or_number'] ?? '—') ?></td>
                        <td><?= format_currency($c['amount']) ?></td>
                        <td>
                            <?php
                            $stBadge = match ($c['request_status']) {
                                'pending', 'processing' => 'badge-yellow',
                                'for_signing', 'ready' => 'badge-blue',
                                'released' => 'badge-green',
                                default => 'badge-gray',
                            };
                            ?>
                            <span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $c['request_status'])) ?></span>
                        </td>
                        <td class="text-end">
                            <div class="table-actions justify-content-end">
                                <?php if (!in_array($c['request_status'], ['released', 'cancelled'])): ?>
                                    <form method="POST" action="<?= admin_url('certificates/sign/' . $c['id']) ?>" class="d-inline" onsubmit="return confirm('Sign and release this certificate?');">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Sign & Release"><i class="bi bi-pen"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($certificates)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No certificates found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= admin_url('certificates?page=' . $i . '&q=' . urlencode($search) . '&type=' . urlencode($type)) ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
