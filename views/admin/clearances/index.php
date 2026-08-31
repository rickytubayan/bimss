<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Clearances</h1>
        <p class="page-subtitle mb-0"><?= number_format($total) ?> record(s)</p>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= admin_url('clearances') ?>" class="btn btn-sm <?= $status === '' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
    <?php
    $statusFilters = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'for_signing' => 'For Signing',
        'ready' => 'Ready',
        'released' => 'Released',
        'cancelled' => 'Cancelled',
    ];
    foreach ($statusFilters as $val => $label):
    ?>
        <a href="<?= admin_url('clearances?status=' . urlencode($val)) ?>" class="btn btn-sm <?= $status === $val ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<form method="GET" action="<?= admin_url('clearances') ?>" class="row g-2 mb-3">
    <?php if ($status !== ''): ?>
        <input type="hidden" name="status" value="<?= e($status) ?>">
    <?php endif; ?>
    <div class="col-md-8">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Search by tracking code or resident name..." value="<?= e($search) ?>">
        </div>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Tracking Code</th>
                    <th>Resident</th>
                    <th>Document Type</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clearances as $row): ?>
                    <?php
                    $statusBadge = match ($row['status']) {
                        'pending' => 'badge-yellow',
                        'processing' => 'badge-blue',
                        'for_signing' => 'badge-blue',
                        'ready' => 'badge-green',
                        'released' => 'badge-gray',
                        'cancelled' => 'badge-red',
                        default => 'badge-gray',
                    };
                    ?>
                    <tr>
                        <td><code><?= e($row['tracking_code']) ?></code></td>
                        <td>
                            <a href="<?= admin_url('residents/' . $row['resident_id']) ?>" class="fw-semibold text-decoration-none">
                                <?= e($row['resident_name']) ?>
                            </a>
                        </td>
                        <td><?= e($row['document_type_name']) ?></td>
                        <td class="text-muted"><?= e(truncate($row['purpose'] ?? '', 40)) ?></td>
                        <td>
                            <span class="badge rounded-pill <?= $statusBadge ?>"><?= ucwords(str_replace('_', ' ', $row['status'])) ?></span>
                        </td>
                        <td class="text-muted"><?= format_date($row['requested_at']) ?></td>
                        <td class="text-end">
                            <div class="table-actions justify-content-end">
                                <a href="<?= admin_url('clearances/' . $row['id']) ?>" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clearances)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No clearances found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3" aria-label="Pagination">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= admin_url('clearances?page=' . $i . '&q=' . urlencode($search) . '&status=' . urlencode($status)) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
