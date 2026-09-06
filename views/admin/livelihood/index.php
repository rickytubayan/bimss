<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Livelihood</h1>
        <p class="page-subtitle mb-0">Job postings & farmer registry</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('livelihood/jobs') ?>" class="btn btn-outline-primary"><i class="bi bi-briefcase me-1"></i>Job Postings</a>
        <a href="<?= admin_url('livelihood/farmers') ?>" class="btn btn-outline-primary"><i class="bi bi-tree me-1"></i>Farmers</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-briefcase"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Job Postings"><?= $stats['jobs'] ?></div>
            <div class="stat-label">Job Postings</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Active Jobs"><?= $stats['active_jobs'] ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Applications"><?= $stats['applications'] ?></div>
            <div class="stat-label">Applications</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-tree"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Active Farmers"><?= $stats['farmers'] ?></div>
            <div class="stat-label">Active Farmers</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-briefcase me-2 text-primary"></i>Recent Job Postings</span>
        <a href="<?= admin_url('livelihood/jobs') ?>" class="btn btn-sm btn-outline-primary">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Company</th>
                    <th>Salary</th>
                    <th>Applications</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentJobs as $j): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($j['position']) ?></td>
                        <td class="text-muted small"><?= e($j['company_name']) ?></td>
                        <td class="text-muted small"><?= e($j['salary_range'] ?? '—') ?></td>
                        <td class="text-center"><?= (int)$j['application_count'] ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('livelihood/jobs') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye me-1"></i>Jobs</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentJobs)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No job postings yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>