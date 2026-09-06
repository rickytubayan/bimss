<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Compliance & Transparency</h1>
        <p class="page-subtitle mb-0">Full disclosure of barangay documents via the public portal</p>
    </div>
    <a href="#uploadDocument" class="btn btn-primary"><i class="bi bi-cloud-arrow-up me-1"></i>Upload Document</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-files"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Documents"><?= $stats['documents'] ?></div>
            <div class="stat-label">Documents</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Posted"><?= $stats['posted'] ?></div>
            <div class="stat-label">Posted</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-calendar-range"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Years Covered"><?= $stats['years'] ?></div>
            <div class="stat-label">Fiscal Years</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Needs Update"><?= $stats['needs_posting'] ?></div>
            <div class="stat-label">Expired / Needs Re-post</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-files me-2 text-primary"></i>Posted Documents</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $type === '' ? 'active' : '' ?>" href="<?= admin_url('compliance') ?>">All</a></li>
                    <?php foreach (array_keys($typeLabels) as $t): ?>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2 <?= $type === $t ? 'active' : '' ?>" href="<?= admin_url('compliance?type=' . $t) ?>"><?= e($typeLabels[$t]) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Type</th>
                            <th>FY</th>
                            <th>Posted</th>
                            <th>Valid Until</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $d): ?>
                            <?php
                            $stBadge = match ($d['status']) {
                                'posted' => 'badge-green',
                                'expired' => 'badge-red',
                                default => 'badge-gray',
                            };
                            $typeBadge = in_array($d['document_type'], ['budget', 'annual_report', 'procurement']) ? 'badge-blue' : 'badge-yellow';
                            ?>
                            <tr>
                                <td class="fw-semibold small"><?= e($d['title']) ?></td>
                                <td><span class="badge rounded-pill <?= $typeBadge ?>"><?= e($typeLabels[$d['document_type']] ?? ucfirst($d['document_type'])) ?></span></td>
                                <td class="text-muted small"><?= $d['fiscal_year'] ?><?= $d['quarter'] ? ' Q' . $d['quarter'] : '' ?></td>
                                <td class="text-muted small"><?= format_date($d['posted_at'], 'M j, Y') ?></td>
                                <td class="text-muted small"><?= $d['valid_until'] ? format_date($d['valid_until']) : '—' ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($d['status']) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= asset($d['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($documents)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No transparency documents posted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4" id="uploadDocument">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-cloud-arrow-up me-2 text-primary"></i>Upload Document</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('compliance/transparency/upload') ?>" enctype="multipart/form-data" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= e(old('title')) ?>" placeholder="e.g. 2026 Annual Budget" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="document_type">Document Type <span class="text-danger">*</span></label>
                        <select id="document_type" name="document_type" class="form-select" required>
                            <option value="">Select a type…</option>
                            <?php foreach ($typeLabels as $t => $lbl): ?>
                                <option value="<?= $t ?>" <?= old('document_type') === $t ? 'selected' : '' ?>><?= e($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="fiscal_year">Fiscal Year <span class="text-danger">*</span></label>
                            <input type="number" min="2000" max="2100" id="fiscal_year" name="fiscal_year" class="form-control" value="<?= e(old('fiscal_year', date('Y'))) ?>" required>
                        </div>
                        <div class="col">
                            <label class="form-label" for="quarter">Quarter</label>
                            <select id="quarter" name="quarter" class="form-select">
                                <option value="">—</option>
                                <?php foreach ([1,2,3,4] as $q): ?>
                                    <option value="<?= $q ?>" <?= old('quarter') == $q ? 'selected' : '' ?>>Q<?= $q ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="file">File <span class="text-danger">*</span></label>
                        <input type="file" id="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                        <div class="form-text">PDF, Word, Excel, or images.</div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="valid_until">Valid Until</label>
                            <input type="date" id="valid_until" name="valid_until" class="form-control" value="<?= e(old('valid_until')) ?>">
                        </div>
                        <div class="col">
                            <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select" required>
                                <?php foreach (['posted', 'expired', 'removed'] as $s): ?>
                                    <option value="<?= $s ?>" <?= old('status') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-cloud-arrow-up me-1"></i>Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>