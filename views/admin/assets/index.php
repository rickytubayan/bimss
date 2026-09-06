<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Barangay Assets</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($assets)) ?> recorded asset<?= count($assets) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= admin_url('drrm') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to DRRM</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-tools"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Assets"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Assets</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-truck"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="In Use"><?= $stats['in_use'] ?></div>
            <div class="stat-label">In Use</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-wrench-adjustable"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Under Repair"><?= $stats['under_repair'] ?></div>
            <div class="stat-label">Under Repair</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-piggy-bank"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Value">₱<?= number_format($stats['value'], 2) ?></div>
            <div class="stat-label">Total Purchase Value</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-tools me-2 text-primary"></i>Assets</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= ($status === '' && $category === '') ? 'active' : '' ?>" href="<?= admin_url('assets') ?>">All</a></li>
                    <?php foreach (['available', 'in_use', 'under_repair', 'disposed'] as $s): ?>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2 <?= ($status === $s && $category === '') ? 'active' : '' ?>" href="<?= admin_url('assets?status=' . $s) ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Category</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th class="text-end">Value</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assets as $a): ?>
                            <?php
                            $condBadge = match ($a['current_condition']) {
                                'excellent', 'good' => 'badge-green',
                                'fair' => 'badge-yellow',
                                default => 'badge-red',
                            };
                            $statusBadge = match ($a['status']) {
                                'available' => 'badge-green',
                                'in_use' => 'badge-blue',
                                'under_repair' => 'badge-yellow',
                                default => 'badge-gray',
                            };
                            $catBadge = in_array($a['category'], ['vehicle', 'equipment', 'facility']) ? 'badge-blue' : 'badge-yellow';
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($a['name']) ?></div>
                                    <div class="text-muted small"><?= $a['maintenance_count'] ?> maintenance record<?= $a['maintenance_count'] === 1 ? '' : 's' ?></div>
                                </td>
                                <td><span class="badge rounded-pill <?= $catBadge ?>"><?= ucfirst($a['category']) ?></span></td>
                                <td><span class="badge rounded-pill <?= $condBadge ?>"><?= ucfirst(str_replace('_', ' ', $a['current_condition'])) ?></span></td>
                                <td><span class="badge rounded-pill <?= $statusBadge ?>"><?= ucfirst(str_replace('_', ' ', $a['status'])) ?></span></td>
                                <td class="text-end fw-semibold">₱<?= number_format($a['purchase_cost'], 2) ?></td>
                                <td class="text-end">
                                    <a href="<?= admin_url('assets/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($assets)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No assets recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Register Asset</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('assets/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="name">Asset Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= e(old('name')) ?>" placeholder="e.g. Barangay Patrol Jeep" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
                        <select id="category" name="category" class="form-select" required>
                            <?php foreach (['vehicle', 'equipment', 'facility', 'furniture', 'other'] as $c): ?>
                                <option value="<?= $c ?>" <?= old('category') === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="purchase_date">Purchase Date</label>
                            <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="<?= e(old('purchase_date')) ?>">
                        </div>
                        <div class="col">
                            <label class="form-label" for="purchase_cost">Cost (₱)</label>
                            <input type="number" step="any" min="0" id="purchase_cost" name="purchase_cost" class="form-control" value="<?= e(old('purchase_cost')) ?>" placeholder="0.00">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="current_condition">Condition <span class="text-danger">*</span></label>
                            <select id="current_condition" name="current_condition" class="form-select" required>
                                <?php foreach (['excellent', 'good', 'fair', 'poor', 'non_functional'] as $c): ?>
                                    <option value="<?= $c ?>" <?= old('current_condition') === $c ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $c)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select" required>
<?php foreach (['available', 'in_use', 'under_repair', 'disposed'] as $s): ?>
                                    <option value="<?= $s ?>" <?= old('status') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="next_maintenance">Next Maintenance</label>
                        <input type="date" id="next_maintenance" name="next_maintenance" class="form-control" value="<?= e(old('next_maintenance')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="2"><?= e(old('description')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Register Asset</button>
                </form>
            </div>
        </div>
    </div>
</div>