<?php
$c = $clearance ?? [];
$cf = function ($key) use ($c) { return old($key, $c[$key] ?? ''); };
?>
<div class="page-header">
    <h1 class="page-title">Edit Clearance #<?= e($c['tracking_code'] ?? '') ?></h1>
    <p class="page-subtitle">Update clearance information</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= admin_url('clearances/' . $c['id'] . '/update') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="resident_id">Resident <span class="text-danger">*</span></label>
                            <select id="resident_id" name="resident_id" class="form-select" required>
                                <option value="">Select resident</option>
                                <?php foreach ($residents as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= (string)$cf('resident_id') === (string)$r['id'] ? 'selected' : '' ?>><?= e($r['last_name'] . ', ' . $r['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="document_type_id">Document Type <span class="text-danger">*</span></label>
                            <select id="document_type_id" name="document_type_id" class="form-select" required>
                                <option value="">Select type</option>
                                <?php foreach ($docTypes as $dt): ?>
                                    <option value="<?= $dt['id'] ?>" <?= (string)$cf('document_type_id') === (string)$dt['id'] ? 'selected' : '' ?>><?= e($dt['name']) ?> (₱<?= number_format($dt['fee'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="purpose">Purpose <span class="text-danger">*</span></label>
                            <textarea id="purpose" name="purpose" class="form-control" rows="3" required><?= e($cf('purpose')) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-select">
                                <?php foreach (['pending', 'processing', 'for_signing', 'ready', 'released', 'cancelled'] as $st): ?>
                                    <option value="<?= $st ?>" <?= ($cf('status') ?: 'pending') === $st ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $st)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="released_to">Released To</label>
                            <input type="text" id="released_to" name="released_to" class="form-control" value="<?= e($cf('released_to')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="representative_name">Representative</label>
                            <input type="text" id="representative_name" name="representative_name" class="form-control" value="<?= e($cf('representative_name')) ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Clearance</button>
                        <a href="<?= admin_url('clearances/' . $c['id']) ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>