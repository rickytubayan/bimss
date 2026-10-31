<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Senior & PWD</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($rows)) ?> registered profile(s)</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-person-hearts"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Senior Citizens"><?= $stats['seniors'] ?></div>
            <div class="stat-label">Senior Citizens</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-person-wheelchair"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="PWDs"><?= $stats['pwd'] ?></div>
            <div class="stat-label">Persons with Disability</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-piggy-bank"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Active Pensioners"><?= $stats['active_pensioners'] ?></div>
            <div class="stat-label">Active Pensioners</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Monthly Allowance"><?= format_currency($stats['monthly_allowance']) ?></div>
            <div class="stat-label">Monthly Allowance (active)</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Register Senior / PWD</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('seniors/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="resident_id">Resident <span class="text-danger">*</span></label>
                        <select id="resident_id" name="resident_id" class="form-select" required>
                            <option value="">Select resident...</option>
                            <?php foreach ($residents as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= old('resident_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                        <select id="type" name="type" class="form-select" required>
                            <option value="senior" <?= old('type') === 'senior' ? 'selected' : '' ?>>Senior Citizen</option>
                            <option value="pwd" <?= old('type') === 'pwd' ? 'selected' : '' ?>>Person with Disability (PWD)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="pension_status">Pension Status <span class="text-danger">*</span></label>
                        <select id="pension_status" name="pension_status" class="form-select" required>
                            <option value="pending" <?= old('pension_status') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="active" <?= old('pension_status') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('pension_status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="monthly_allowance">Monthly Allowance</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" id="monthly_allowance" name="monthly_allowance" class="form-control" value="<?= e(old('monthly_allowance')) ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="grocery_benefits">Grocery Benefits</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" id="grocery_benefits" name="grocery_benefits" class="form-control" value="<?= e(old('grocery_benefits')) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="id_type">ID Type</label>
                            <select id="id_type" name="id_type" class="form-select form-select-sm">
                                <option value="">—</option>
                                <option value="osca" <?= old('id_type') === 'osca' ? 'selected' : '' ?>>OSCA</option>
                                <option value="pwd" <?= old('id_type') === 'pwd' ? 'selected' : '' ?>>PWD</option>
                                <option value="national" <?= old('id_type') === 'national' ? 'selected' : '' ?>>National ID</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="id_number">ID Number</label>
                            <input type="text" id="id_number" name="id_number" class="form-control form-control-sm" value="<?= e(old('id_number')) ?>">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="osca_number">OSCA Number</label>
                            <input type="text" id="osca_number" name="osca_number" class="form-control form-control-sm" value="<?= e(old('osca_number')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="pwd_number">PWD Number</label>
                            <input type="text" id="pwd_number" name="pwd_number" class="form-control form-control-sm" value="<?= e(old('pwd_number')) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3"><i class="bi bi-check-lg me-1"></i>Save Profile</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-people me-2 text-primary"></i>Registered Profiles</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $type === '' ? 'active' : '' ?>" href="<?= admin_url('seniors') ?>">All</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $type === 'senior' ? 'active' : '' ?>" href="<?= admin_url('seniors?type=senior') ?>">Seniors</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $type === 'pwd' ? 'active' : '' ?>" href="<?= admin_url('seniors?type=pwd') ?>">PWDs</a></li>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>Type</th>
                            <th>ID</th>
                            <th>Pension Status</th>
                            <th>Monthly Allowance</th>
                            <th>Grocery</th>
                            <th>OSCA / PWD No.</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $typeBadge = $r['type'] === 'senior' ? 'badge-blue' : 'badge-yellow';
                            $stBadge = match ($r['pension_status']) {
                                'active' => 'badge-green',
                                'inactive' => 'badge-gray',
                                default => 'badge-yellow',
                            };
                            $idLine = '';
                            if (!empty($r['id_type'])) $idLine = strtoupper($r['id_type']);
                            if (!empty($r['id_number'])) $idLine .= ($idLine !== '' ? ': ' : '') . '#' . $r['id_number'];
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e($r['resident_name']) ?></td>
                                <td><span class="badge rounded-pill <?= $typeBadge ?>"><?= $r['type'] === 'senior' ? 'Senior' : 'PWD' ?></span></td>
                                <td class="text-muted small"><?= e($idLine !== '' ? $idLine : '—') ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($r['pension_status']) ?></span></td>
                                <td><?= format_currency($r['monthly_allowance']) ?></td>
                                <td class="text-muted"><?= $r['grocery_benefits'] > 0 ? format_currency($r['grocery_benefits']) : '—' ?></td>
                                <td class="text-muted small">
                                    <?= e($r['osca_number'] ?? ($r['pwd_number'] ?? '')) ?: '—' ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfile<?= (int)$r['id'] ?>" title="Edit profile">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="<?= admin_url('seniors/' . (int)$r['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this senior/PWD profile?');">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete profile"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No senior/PWD profiles registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$allResidentIds = array_map('intval', array_column($allResidents, 'id'));
foreach ($rows as $r):
    $rid = (int)$r['id'];
    $currentResidentInList = in_array((int)$r['resident_id'], $allResidentIds, true);
?>
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfile<?= $rid ?>" tabindex="-1" aria-labelledby="editProfileLabel<?= $rid ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="<?= admin_url('seniors/' . $rid . '/update') ?>">
                    <?= CSRF::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileLabel<?= $rid ?>"><i class="bi bi-pencil me-2 text-primary"></i>Edit Profile &mdash; <?= e($r['resident_name']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="resident_id_<?= $rid ?>">Resident <span class="text-danger">*</span></label>
                                <select id="resident_id_<?= $rid ?>" name="resident_id" class="form-select" required>
                                    <option value="">Select resident...</option>
                                    <?php if (!$currentResidentInList): ?>
                                        <option value="<?= (int)$r['resident_id'] ?>" selected><?= e($r['resident_name']) ?> (current)</option>
                                    <?php endif; ?>
                                    <?php foreach ($allResidents as $res): ?>
                                        <option value="<?= (int)$res['id'] ?>" <?= (int)$r['resident_id'] === (int)$res['id'] ? 'selected' : '' ?>>
                                            <?= e($res['label']) ?><?= $res['status'] !== 'active' ? ' (' . ucfirst($res['status']) . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="type_<?= $rid ?>">Type <span class="text-danger">*</span></label>
                                <select id="type_<?= $rid ?>" name="type" class="form-select" required>
                                    <option value="senior" <?= $r['type'] === 'senior' ? 'selected' : '' ?>>Senior Citizen</option>
                                    <option value="pwd" <?= $r['type'] === 'pwd' ? 'selected' : '' ?>>Person with Disability (PWD)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pension_status_<?= $rid ?>">Pension Status <span class="text-danger">*</span></label>
                                <select id="pension_status_<?= $rid ?>" name="pension_status" class="form-select" required>
                                    <option value="pending" <?= $r['pension_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="active" <?= $r['pension_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $r['pension_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="monthly_allowance_<?= $rid ?>">Monthly Allowance</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" min="0" id="monthly_allowance_<?= $rid ?>" name="monthly_allowance" class="form-control" value="<?= e($r['monthly_allowance']) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="grocery_benefits_<?= $rid ?>">Grocery Benefits</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" min="0" id="grocery_benefits_<?= $rid ?>" name="grocery_benefits" class="form-control" value="<?= e($r['grocery_benefits']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="id_type_<?= $rid ?>">ID Type</label>
                                <select id="id_type_<?= $rid ?>" name="id_type" class="form-select">
                                    <option value="">—</option>
                                    <option value="osca" <?= $r['id_type'] === 'osca' ? 'selected' : '' ?>>OSCA</option>
                                    <option value="pwd" <?= $r['id_type'] === 'pwd' ? 'selected' : '' ?>>PWD</option>
                                    <option value="national" <?= $r['id_type'] === 'national' ? 'selected' : '' ?>>National ID</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="id_number_<?= $rid ?>">ID Number</label>
                                <input type="text" id="id_number_<?= $rid ?>" name="id_number" class="form-control" value="<?= e($r['id_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="osca_number_<?= $rid ?>">OSCA Number</label>
                                <input type="text" id="osca_number_<?= $rid ?>" name="osca_number" class="form-control" value="<?= e($r['osca_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pwd_number_<?= $rid ?>">PWD Number</label>
                                <input type="text" id="pwd_number_<?= $rid ?>" name="pwd_number" class="form-control" value="<?= e($r['pwd_number'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>