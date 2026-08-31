<div class="page-header">
    <h1 class="page-title">Create Certificate</h1>
    <p class="page-subtitle">Issue a new certificate for a resident</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= admin_url('certificates/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="resident_id">Resident <span class="text-danger">*</span></label>
                            <select id="resident_id" name="resident_id" class="form-select" required>
                                <option value="">Select resident</option>
                                <?php foreach ($residents as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= old('resident_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['last_name'] . ', ' . $r['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="certificate_type">Type <span class="text-danger">*</span></label>
                            <select id="certificate_type" name="certificate_type" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach (['residency','indigency','good_moral','jobseeker','birth','other'] as $ct): ?>
                                    <option value="<?= $ct ?>" <?= old('certificate_type') === $ct ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $ct)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="purpose">Purpose</label>
                            <input type="text" id="purpose" name="purpose" class="form-control" value="<?= e(old('purpose')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="or_number">OR Number</label>
                            <input type="text" id="or_number" name="or_number" class="form-control" value="<?= e(old('or_number')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="amount">Amount (₱)</label>
                            <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" value="<?= e(old('amount', '0.00')) ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Certificate</button>
                        <a href="<?= admin_url('certificates') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
