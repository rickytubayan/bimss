<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Child Growth Records</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($records)) ?> record(s)</p>
    </div>
    <a href="<?= admin_url('health') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Health</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Growth Record</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('health/growth/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="child_resident_id">Child Resident <span class="text-danger">*</span></label>
                        <select id="child_resident_id" name="child_resident_id" class="form-select" required>
                            <option value="">Select child...</option>
                            <?php foreach ($residents as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= old('child_resident_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="age_months">Age (months) <span class="text-danger">*</span></label>
                        <input type="number" id="age_months" name="age_months" class="form-control" min="0" value="<?= e(old('age_months')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="weight_kg">Weight (kg) <span class="text-danger">*</span></label>
                        <input type="number" id="weight_kg" name="weight_kg" class="form-control" step="0.01" min="0.01" value="<?= e(old('weight_kg')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="height_cm">Height (cm) <span class="text-danger">*</span></label>
                        <input type="number" id="height_cm" name="height_cm" class="form-control" step="0.01" min="0.01" value="<?= e(old('height_cm')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="head_circumference">Head Circumference</label>
                        <input type="number" id="head_circumference" name="head_circumference" class="form-control" step="0.01" min="0" value="<?= e(old('head_circumference')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="nutrition_status">Nutrition Status <span class="text-danger">*</span></label>
                        <select id="nutrition_status" name="nutrition_status" class="form-select" required>
                            <?php foreach (['normal', 'overweight', 'underweight', 'severely_underweight', 'wasted', 'stunted'] as $ns): ?>
                                <option value="<?= $ns ?>" <?= old('nutrition_status') === $ns ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $ns)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="recorded_by">Recorded By</label>
                        <input type="text" id="recorded_by" name="recorded_by" class="form-control" value="<?= e(old('recorded_by')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Record</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-activity me-2 text-primary"></i>All Growth Records</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Child</th>
                            <th>Age (mo)</th>
                            <th>Weight</th>
                            <th>Height</th>
                            <th>Head Circ.</th>
                            <th>Nutrition Status</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <?php
                            $stBadge = match ($r['nutrition_status']) {
                                'normal' => 'badge-green',
                                'overweight' => 'badge-yellow',
                                default => 'badge-red',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e($r['child_name']) ?></td>
                                <td><?= (int)$r['age_months'] ?></td>
                                <td class="text-muted"><?= number_format($r['weight_kg'], 2) ?> kg</td>
                                <td class="text-muted"><?= number_format($r['height_cm'], 2) ?> cm</td>
                                <td class="text-muted"><?= e($r['head_circumference'] ? number_format($r['head_circumference'], 2) . ' cm' : '—') ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $r['nutrition_status'])) ?></span></td>
                                <td class="text-muted"><?= e($r['recorded_by'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No child growth records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>