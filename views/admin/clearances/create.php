<div class="page-header">
    <h1 class="page-title">Add Clearance</h1>
    <p class="page-subtitle">Issue a new clearance for a resident</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= admin_url('clearances/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="resident_id">Resident <span class="text-danger">*</span></label>
                            <select id="resident_id" name="resident_id" class="form-select" required>
                                <option value="">Select resident</option>
                                <?php foreach ($residents as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= old('resident_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['last_name'] . ', ' . $r['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="document_type_id">Document Type <span class="text-danger">*</span></label>
                            <select id="document_type_id" name="document_type_id" class="form-select" required>
                                <option value="">Select type</option>
                                <?php foreach ($docTypes as $dt): ?>
                                    <option value="<?= $dt['id'] ?>" <?= old('document_type_id') == $dt['id'] ? 'selected' : '' ?>>
                                        <?= e($dt['name']) ?> (₱<?= number_format($dt['fee'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="purpose">Purpose <span class="text-danger">*</span></label>
                            <textarea id="purpose" name="purpose" class="form-control" rows="3" required><?= e(old('purpose')) ?></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Clearance</button>
                        <a href="<?= admin_url('clearances') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>