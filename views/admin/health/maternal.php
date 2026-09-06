<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Maternal Records</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($records)) ?> record(s)</p>
    </div>
    <a href="<?= admin_url('health') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Health</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Maternal Record</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('health/maternal/store') ?>" novalidate>
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
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="pregnant" <?= old('status') === 'pregnant' ? 'selected' : '' ?>>Pregnant</option>
                            <option value="delivered" <?= old('status') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="postpartum" <?= old('status') === 'postpartum' ? 'selected' : '' ?>>Postpartum</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="lmp_date">LMP Date</label>
                        <input type="date" id="lmp_date" name="lmp_date" class="form-control" value="<?= e(old('lmp_date')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="expected_due_date">Expected Due Date</label>
                        <input type="date" id="expected_due_date" name="expected_due_date" class="form-control" value="<?= e(old('expected_due_date')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="birth_date">Birth Date</label>
                        <input type="date" id="birth_date" name="birth_date" class="form-control" value="<?= e(old('birth_date')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="birth_weight">Birth Weight (kg)</label>
                        <input type="number" id="birth_weight" name="birth_weight" class="form-control" step="0.01" min="0" value="<?= e(old('birth_weight')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="attending_midwife">Attending Midwife</label>
                        <input type="text" id="attending_midwife" name="attending_midwife" class="form-control" value="<?= e(old('attending_midwife')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="complications">Complications</label>
                        <input type="text" id="complications" name="complications" class="form-control" value="<?= e(old('complications')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Record</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-person-hearts me-2 text-primary"></i>All Maternal Records</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>LMP</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Birth Date</th>
                            <th>Weight</th>
                            <th>Midwife</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <?php
                            $stBadge = match ($r['status']) {
                                'delivered' => 'badge-green',
                                'postpartum' => 'badge-yellow',
                                default => 'badge-blue',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e($r['resident_name']) ?></td>
                                <td class="text-muted small"><?= e($r['lmp_date'] ? format_date($r['lmp_date']) : '—') ?></td>
                                <td class="text-muted small"><?= e($r['expected_due_date'] ? format_date($r['expected_due_date']) : '—') ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($r['status']) ?></span></td>
                                <td class="text-muted small"><?= e($r['birth_date'] ? format_date($r['birth_date']) : '—') ?></td>
                                <td class="text-muted"><?= e($r['birth_weight'] ? number_format($r['birth_weight'], 2) . ' kg' : '—') ?></td>
                                <td class="text-muted"><?= e($r['attending_midwife'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No maternal records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>