<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Households</h1>
        <p class="page-subtitle mb-0"><?= number_format($total) ?> record(s)</p>
    </div>
    <a href="<?= admin_url('households/create') ?>" class="btn btn-primary"><i class="bi bi-house-plus me-1"></i>Add Household</a>
</div>

<form method="GET" action="<?= admin_url('households') ?>" class="row g-2 mb-3">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Search street or house number..." value="<?= e($search) ?>">
        </div>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            <?php foreach (['active', 'vacant', 'demolished'] as $st): ?>
                <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucwords($st) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="classification" class="form-select">
            <option value="">All classifications</option>
            <?php foreach (['residential', 'commercial', 'industrial', 'mixed'] as $cl): ?>
                <option value="<?= $cl ?>" <?= $classification === $cl ? 'selected' : '' ?>><?= ucwords($cl) ?></option>
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
                <tr>
                    <th>ID</th>
                    <th>Address</th>
                    <th>Purok</th>
                    <th>Classification</th>
                    <th>Members</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($households as $h): ?>
                    <tr>
                        <td class="text-muted">#<?= $h['id'] ?></td>
                        <td>
                            <a href="<?= admin_url('households/' . $h['id']) ?>" class="fw-semibold text-decoration-none">
                                <?= e(trim(($h['street'] ?? '') . ' ' . ($h['house_number'] ?? ''))) ?: ('Household #' . $h['id']) ?>
                            </a>
                        </td>
                        <td class="text-muted"><?= e($h['purok_name'] ?? '—') ?></td>
                        <td class="text-capitalize"><?= e($h['classification']) ?></td>
                        <td><?= (int)$h['member_count'] ?></td>
                        <td>
                            <?php
                            $stBadge = match ($h['status']) {
                                'active' => 'badge-green',
                                'vacant' => 'badge-yellow',
                                'demolished' => 'badge-gray',
                                default => 'badge-gray',
                            };
                            ?>
                            <span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords($h['status']) ?></span>
                        </td>
                        <td class="text-end">
                            <div class="table-actions justify-content-end">
                                <a href="<?= admin_url('households/' . $h['id']) ?>" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                                <a href="<?= admin_url('households/' . $h['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="<?= admin_url('households/' . $h['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this household?');">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($households)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No households found.</td></tr>
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
                <a class="page-link" href="<?= admin_url('households?page=' . $i . '&q=' . urlencode($search) . '&status=' . urlencode($status) . '&classification=' . urlencode($classification)) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
