<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Job Postings</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($rows)) ?> posting<?= count($rows) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= admin_url('livelihood') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Livelihood</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-briefcase me-2 text-primary"></i>All Postings</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $filter === '' ? 'active' : '' ?>" href="<?= admin_url('livelihood/jobs') ?>">All</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $filter === 'active' ? 'active' : '' ?>" href="<?= admin_url('livelihood/jobs?filter=active') ?>">Active</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $filter === 'expired' ? 'active' : '' ?>" href="<?= admin_url('livelihood/jobs?filter=expired') ?>">Expired</a></li>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Salary</th>
                            <th>Posted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $j): ?>
                            <?php $active = $j['is_active'] && ($j['expires_at'] === null || strtotime($j['expires_at']) > time()); ?>
                            <tr>
                                <td class="fw-semibold"><?= e($j['position']) ?></td>
                                <td class="text-muted small"><?= e($j['company_name']) ?></td>
                                <td class="text-muted small"><?= e($j['salary_range'] ?? '—') ?></td>
                                <td class="text-muted small"><?= format_date($j['posted_at'], 'M j, Y') ?></td>
                                <td><span class="badge rounded-pill <?= $active ? 'badge-green' : 'badge-gray' ?>"><?= $active ? 'Active' : 'Inactive' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No job postings yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Post a Job</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('livelihood/jobs/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="company_name">Company <span class="text-danger">*</span></label>
                        <input type="text" id="company_name" name="company_name" class="form-control" value="<?= e(old('company_name')) ?>" placeholder="e.g. Local Cooperative" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="position">Position <span class="text-danger">*</span></label>
                        <input type="text" id="position" name="position" class="form-control" value="<?= e(old('position')) ?>" placeholder="e.g. Farm Technician" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="salary_range">Salary Range</label>
                        <input type="text" id="salary_range" name="salary_range" class="form-control" value="<?= e(old('salary_range')) ?>" placeholder="e.g. ₱15,000 – ₱18,000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="expires_at">Expires</label>
                        <input type="date" id="expires_at" name="expires_at" class="form-control" value="<?= e(old('expires_at')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="2"><?= e(old('description')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="requirements">Requirements</label>
                        <textarea id="requirements" name="requirements" class="form-control" rows="2"><?= e(old('requirements')) ?></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active') ? 'checked' : 'checked' ?>>
                        <label class="form-check-label" for="is_active">Active posting</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Post Job</button>
                </form>
            </div>
        </div>
    </div>
</div>