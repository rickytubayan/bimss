<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Residents</h1>
        <p class="page-subtitle mb-0"><?= number_format($total) ?> record(s)</p>
    </div>
    <a href="<?= admin_url('residents/create') ?>" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add Resident</a>
</div>

<form method="GET" action="<?= admin_url('residents') ?>" class="row g-2 mb-3">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Search name or National ID..." value="<?= e($search) ?>">
        </div>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            <?php foreach (['active', 'inactive', 'moved_out', 'deceased'] as $st): ?>
                <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucwords($st) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Purok</th>
                    <th>Sex</th>
                    <th>Age</th>
                    <th>Flags</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($residents as $res): ?>
                    <?php
                        $age = '';
                        if ($res['birthdate']) {
                            $age = date_diff(date_create($res['birthdate']), date_create('today'))->y;
                        }
                    ?>
                    <tr>
                        <td>
                            <a href="<?= admin_url('residents/' . $res['id']) ?>" class="fw-semibold text-decoration-none">
                                <?= e(trim(($res['first_name'] ?? '') . ' ' . ($res['middle_name'] ?? '') . ' ' . $res['last_name'] . ($res['suffix'] ? ' ' . $res['suffix'] : ''))) ?>
                            </a>
                        </td>
                        <td class="text-muted"><?= e($res['purok_name'] ?? '—') ?></td>
                        <td class="text-capitalize"><?= e($res['sex']) ?></td>
                        <td><?= $age !== '' ? $age : '—' ?></td>
                        <td>
                            <span class="d-inline-flex gap-1">
                                <?php if ($res['is_senior']): ?><span class="badge rounded-pill badge-yellow" title="Senior">Sr</span><?php endif; ?>
                                <?php if ($res['is_pwd']): ?><span class="badge rounded-pill badge-blue" title="PWD">PWD</span><?php endif; ?>
                                <?php if ($res['is_voter']): ?><span class="badge rounded-pill badge-green" title="Voter">V</span><?php endif; ?>
                                <?php if (!$res['is_senior'] && !$res['is_pwd'] && !$res['is_voter']): ?><span class="text-muted">—</span><?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $stBadge = match ($res['status']) {
                                'active' => 'badge-green',
                                'inactive', 'moved_out' => 'badge-yellow',
                                'deceased' => 'badge-gray',
                                default => 'badge-gray',
                            };
                            ?>
                            <span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $res['status'])) ?></span>
                        </td>
                        <td class="text-end">
                            <div class="table-actions justify-content-end">
                                <a href="<?= admin_url('residents/' . $res['id']) ?>" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                                <a href="<?= admin_url('residents/' . $res['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="<?= admin_url('residents/' . $res['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this resident?');">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($residents)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No residents found.</td></tr>
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
                <a class="page-link" href="<?= admin_url('residents?page=' . $i . '&q=' . urlencode($search) . '&status=' . urlencode($status)) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
