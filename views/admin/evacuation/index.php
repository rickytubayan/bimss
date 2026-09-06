<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Evacuation Centers</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($centers)) ?> registered center<?= count($centers) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= admin_url('drrm') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to DRRM</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-building"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Centers"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Centers</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-door-open"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Open Centers"><?= $stats['open'] ?></div>
            <div class="stat-label">Open</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-x-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Full Centers"><?= $stats['full'] ?></div>
            <div class="stat-label">Full</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Currently Evacuated"><?= $stats['evacuated'] ?></div>
            <div class="stat-label">Currently Evacuated</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-building me-2 text-primary"></i>Centers</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $status === '' ? 'active' : '' ?>" href="<?= admin_url('evacuation') ?>">All</a></li>
                    <?php foreach (['open','closed','full','maintenance'] as $s): ?>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2 <?= $status === $s ? 'active' : '' ?>" href="<?= admin_url('evacuation?status=' . $s) ?>"><?= ucfirst($s) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Center</th>
                            <th>Occupancy</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($centers as $c): ?>
                            <?php
                            $statusBadge = match ($c['status']) {
                                'open' => 'badge-green',
                                'full' => 'badge-red',
                                'closed' => 'badge-gray',
                                default => 'badge-yellow',
                            };
                            $pct = $c['max_capacity'] > 0 ? round(($c['occupant_count'] ?? 0) / $c['max_capacity'] * 100) : 0;
                            $barColor = $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success');
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($c['name']) ?></div>
                                    <div class="text-muted small"><?= e($c['address'] ?? '—') ?></div>
                                </td>
                                <td style="min-width:150px">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span><?= (int)$c['occupant_count'] ?> / <?= (int)$c['max_capacity'] ?></span>
                                        <span class="text-muted"><?= $pct ?>%</span>
                                    </div>
                                    <div class="progress" style="height:6px">
                                        <div class="progress-bar <?= $barColor ?>" style="width:<?= min($pct, 100) ?>%"></div>
                                    </div>
                                </td>
                                <td><span class="badge rounded-pill <?= $statusBadge ?>"><?= ucfirst($c['status']) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= admin_url('evacuation/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($centers)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No evacuation centers registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Register Center</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('evacuation/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="name">Center Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= e(old('name')) ?>" placeholder="e.g. Barangay Gymnasium" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?= e(old('address')) ?>" placeholder="Street / landmark">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="max_capacity">Max Capacity <span class="text-danger">*</span></label>
                        <input type="number" id="max_capacity" name="max_capacity" class="form-control" min="0" value="<?= e(old('max_capacity', '100')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <?php foreach (['open','closed','full','maintenance'] as $s): ?>
                                <option value="<?= $s ?>" <?= old('status') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Facilities</label>
                        <div class="row row-cols-2 g-1">
                            <?php foreach (['water'=>'Water','toilet'=>'Toilet','sleeping_area'=>'Sleeping Area','kitchen'=>'Kitchen','medical'=>'Medical','generator'=>'Generator','wifi'=>'Wi-Fi','prayer_area'=>'Prayer Area'] as $k => $lbl): ?>
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[<?= $k ?>]" value="1" id="fac_<?= $k ?>" <?= (!empty(old('facilities')[$k]) || ($k === 'water') || ($k === 'toilet')) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="fac_<?= $k ?>"><?= $lbl ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Register</button>
                </form>
            </div>
        </div>
    </div>
</div>