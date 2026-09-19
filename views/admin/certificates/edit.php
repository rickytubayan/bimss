<?php
$c = $certificate ?? [];
$cf = function ($key) use ($c) { return old($key, $c[$key] ?? ''); };
?>
<div class="page-header">
    <h1 class="page-title">Edit Certificate <?= e($c['tracking_code'] ?? ('#' . $c['id'])) ?></h1>
    <p class="page-subtitle">Update certificate information</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= admin_url('certificates/' . $c['id'] . '/update') ?>" novalidate>
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
                            <label class="form-label" for="certificate_type">Type <span class="text-danger">*</span></label>
                            <select id="certificate_type" name="certificate_type" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach (['residency','indigency','good_moral','jobseeker','birth','other'] as $ct): ?>
                                    <option value="<?= $ct ?>" <?= $cf('certificate_type') === $ct ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $ct)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="document_type_id">Document Type</label>
                            <select id="document_type_id" name="document_type_id" class="form-select">
                                <option value="">Select</option>
                                <?php foreach ($docTypes as $dt): ?>
                                    <option value="<?= $dt['id'] ?>" <?= (string)$cf('document_type_id') === (string)$dt['id'] ? 'selected' : '' ?>><?= e($dt['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="request_status">Request Status</label>
                            <select id="request_status" name="request_status" class="form-select">
                                <?php foreach (['pending', 'processing', 'for_signing', 'ready', 'released', 'cancelled'] as $st): ?>
                                    <option value="<?= $st ?>" <?= ($cf('request_status') ?: 'processing') === $st ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $st)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="purpose">Purpose</label>
                            <input type="text" id="purpose" name="purpose" class="form-control" value="<?= e($cf('purpose')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="or_number">OR Number</label>
                            <input type="text" id="or_number" name="or_number" class="form-control" value="<?= e($cf('or_number')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="amount">Amount (₱)</label>
                            <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" value="<?= e($cf('amount')) ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Certificate</button>
                        <a href="<?= admin_url('certificates/' . $c['id']) ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>