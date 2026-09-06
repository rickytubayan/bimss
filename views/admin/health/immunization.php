<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Immunization Records</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($records)) ?> record(s)</p>
    </div>
    <a href="<?= admin_url('health') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Health</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Immunization Record</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('health/immunization/store') ?>" novalidate>
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
                        <label class="form-label" for="vaccine_name">Vaccine <span class="text-danger">*</span></label>
                        <input type="text" id="vaccine_name" name="vaccine_name" class="form-control" value="<?= e(old('vaccine_name')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="dose_number">Dose # <span class="text-danger">*</span></label>
                        <input type="number" id="dose_number" name="dose_number" class="form-control" min="1" value="<?= e(old('dose_number')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="date_administered">Date Administered <span class="text-danger">*</span></label>
                        <input type="date" id="date_administered" name="date_administered" class="form-control" value="<?= e(old('date_administered') ?: date('Y-m-d')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="batch_number">Batch Number</label>
                        <input type="text" id="batch_number" name="batch_number" class="form-control" value="<?= e(old('batch_number')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="next_schedule">Next Schedule</label>
                        <input type="date" id="next_schedule" name="next_schedule" class="form-control" value="<?= e(old('next_schedule')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="administered_by">Administered By</label>
                        <input type="text" id="administered_by" name="administered_by" class="form-control" value="<?= e(old('administered_by')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Record</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-droplet-half me-2 text-primary"></i>All Immunization Records</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Child</th>
                            <th>Vaccine</th>
                            <th>Dose</th>
                            <th>Date Administered</th>
                            <th>Batch</th>
                            <th>Next Schedule</th>
                            <th>Administered By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($r['child_name']) ?></td>
                                <td><?= e($r['vaccine_name']) ?></td>
                                <td><span class="badge rounded-pill badge-blue">Dose <?= (int)$r['dose_number'] ?></span></td>
                                <td class="text-muted small"><?= format_date($r['date_administered']) ?></td>
                                <td class="text-muted"><?= e($r['batch_number'] ?? '—') ?></td>
                                <td class="text-muted small"><?= e($r['next_schedule'] ? format_date($r['next_schedule']) : '—') ?></td>
                                <td class="text-muted"><?= e($r['administered_by'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No immunization records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>