<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Farmer Registry</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($rows)) ?> registered farmer<?= count($rows) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= admin_url('livelihood') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Livelihood</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-tree"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Farmers"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Farmers</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Active Farmers"><?= $stats['active'] ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-globe-asia-australia"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Farm Hectares"><?= number_format($stats['hectares'], 2) ?></div>
            <div class="stat-label">Farm Hectares</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-tree me-2 text-primary"></i>Registered Farmers</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $status === '' ? 'active' : '' ?>" href="<?= admin_url('livelihood/farmers') ?>">All</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $status === 'active' ? 'active' : '' ?>" href="<?= admin_url('livelihood/farmers?status=active') ?>">Active</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $status === 'inactive' ? 'active' : '' ?>" href="<?= admin_url('livelihood/farmers?status=inactive') ?>">Inactive</a></li>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Farmer</th>
                            <th>Registered</th>
                            <th class="text-end">Farm Size</th>
                            <th>Primary Crops</th>
                            <th>Livestock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $f): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($f['farmer_name']) ?></td>
                                <td class="text-muted small"><?= format_date($f['registration_date']) ?></td>
                                <td class="text-end fw-semibold"><?= number_format($f['farm_size_hectares'], 2) ?> ha</td>
                                <td class="small text-muted"><?= e($f['primary_crops'] ?? '—') ?></td>
                                <td class="small text-muted"><?= e($f['livestock'] ?? '—') ?></td>
                                <td><span class="badge rounded-pill <?= $f['status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= ucfirst($f['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No farmers registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Register Farmer</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('livelihood/farmers/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="resident_id">Farmer Resident <span class="text-danger">*</span></label>
                        <select id="resident_id" name="resident_id" class="form-select" required>
                            <option value="">Select a resident…</option>
                            <?php foreach ($residents as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= in_array($r['id'], $registered) ? 'disabled' : '' ?> <?= old('resident_id') == $r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['name']) ?><?= in_array($r['id'], $registered) ? ' (registered)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="farm_size_hectares">Farm Size (ha)</label>
                            <input type="number" step="any" min="0" id="farm_size_hectares" name="farm_size_hectares" class="form-control" value="<?= e(old('farm_size_hectares')) ?>" placeholder="0.00">
                        </div>
                        <div class="col">
                            <label class="form-label" for="registration_date">Registered <span class="text-danger">*</span></label>
                            <input type="date" id="registration_date" name="registration_date" class="form-control" value="<?= e(old('registration_date', date('Y-m-d'))) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="primary_crops">Primary Crops</label>
                        <input type="text" id="primary_crops" name="primary_crops" class="form-control" value="<?= e(old('primary_crops')) ?>" placeholder="e.g. Rice, Corn">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="livestock">Livestock</label>
                        <input type="text" id="livestock" name="livestock" class="form-control" value="<?= e(old('livestock')) ?>" placeholder="e.g. Pigs, Chickens">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Register Farmer</button>
                </form>
            </div>
        </div>
    </div>
</div>