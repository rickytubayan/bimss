<?php
$categories = ['general','health','safety','event','emergency','job'];
$catColors = ['general'=>'secondary','health'=>'success','safety'=>'warning','event'=>'info','emergency'=>'danger','job'=>'primary'];
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Bulletins</h1>
        <p class="page-subtitle mb-0"><?= number_format($total) ?> post(s)</p>
    </div>
    <a href="<?= admin_url('bulletins/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Bulletin</a>
</div>

<form method="GET" action="<?= admin_url('bulletins') ?>" class="row g-2 mb-3">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Search title or content..." value="<?= e($search) ?>">
        </div>
    </div>
    <div class="col-md-3">
        <select name="category" class="form-select">
            <option value="">All categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
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
                <tr><th>Title</th><th>Category</th><th>Posted By</th><th>Published</th><th>Pin</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($bulletins as $b): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($b['title']) ?></td>
                        <td><span class="badge rounded-pill text-bg-<?= $catColors[$b['category']] ?? 'secondary' ?>"><?= ucfirst($b['category']) ?></span></td>
                        <td class="text-muted"><?= e($b['posted_by_name'] ?? '—') ?></td>
                        <td class="text-muted small"><?= format_date($b['published_at']) ?></td>
                        <td><?= $b['is_pinned'] ? '<i class="bi bi-pin-fill text-warning"></i>' : '' ?></td>
                        <td class="text-end">
                            <form method="POST" action="<?= admin_url('bulletins/' . $b['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this bulletin?');">
                                <?= CSRF::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bulletins)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No bulletins found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= admin_url('bulletins?page=' . $i . '&q=' . urlencode($search) . '&category=' . urlencode($category)) ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
