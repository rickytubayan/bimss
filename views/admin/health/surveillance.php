<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Disease Surveillance</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($records)) ?> record(s)</p>
    </div>
    <a href="<?= admin_url('health') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Health</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Surveillance Record</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('health/surveillance/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="disease_name">Disease <span class="text-danger">*</span></label>
                        <input type="text" id="disease_name" name="disease_name" class="form-control" value="<?= e(old('disease_name')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="date_reported">Date Reported <span class="text-danger">*</span></label>
                        <input type="date" id="date_reported" name="date_reported" class="form-control" value="<?= e(old('date_reported') ?: date('Y-m-d')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <?php foreach (['suspected', 'confirmed', 'recovered', 'deceased'] as $st): ?>
                                <option value="<?= $st ?>" <?= old('status') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="purok_id">Purok</label>
                        <select id="purok_id" name="purok_id" class="form-select">
                            <option value="">No purok</option>
                            <?php foreach ($puroks as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= old('purok_id') == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="patient_age">Patient Age</label>
                        <input type="number" id="patient_age" name="patient_age" class="form-control" min="0" max="120" value="<?= e(old('patient_age')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="patient_sex">Patient Sex</label>
                        <select id="patient_sex" name="patient_sex" class="form-select">
                            <option value="">—</option>
                            <option value="male" <?= old('patient_sex') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= old('patient_sex') === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="reported_to_doctor" id="reported_to_doctor" value="1" <?= old('reported_to_doctor') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="reported_to_doctor">Reported to Doctor</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <input type="text" id="notes" name="notes" class="form-control" value="<?= e(old('notes')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Record</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-bug me-2 text-primary"></i>All Surveillance Records</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Disease</th>
                            <th>Date Reported</th>
                            <th>Purok</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Status</th>
                            <th>Doctor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <?php
                            $stBadge = match ($r['status']) {
                                'confirmed' => 'badge-red',
                                'recovered' => 'badge-green',
                                'deceased' => 'badge-gray',
                                default => 'badge-yellow',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e(ucwords($r['disease_name'])) ?></td>
                                <td class="text-muted small"><?= format_date($r['date_reported']) ?></td>
                                <td class="text-muted"><?= e($r['purok_name'] ?? '—') ?></td>
                                <td><?= e($r['patient_age'] ?? '—') ?></td>
                                <td class="text-capitalize"><?= e($r['patient_sex'] ?? '—') ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($r['status']) ?></span></td>
                                <td>
                                    <?php if ($r['reported_to_doctor']): ?>
                                        <span class="badge rounded-pill badge-green"><i class="bi bi-check-lg"></i> Yes</span>
                                    <?php else: ?>
                                        <span class="text-muted">No</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No disease surveillance records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>