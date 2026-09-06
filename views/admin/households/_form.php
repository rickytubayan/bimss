<?php
$h = $household ?? [];
function hf($key) { return old($key, $h[$key] ?? ''); }
?>
<form method="POST" action="<?= $formAction ?>" novalidate>
    <?= CSRF::field() ?>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex align-items-center"><i class="bi bi-house me-2 text-primary"></i>Address</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="house_number">House Number</label>
                    <input type="text" id="house_number" name="house_number" class="form-control" value="<?= e(hf('house_number')) ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="street">Street <span class="text-danger">*</span></label>
                    <input type="text" id="street" name="street" class="form-control" value="<?= e(hf('street')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="purok_id">Purok / Sitio</label>
                    <select id="purok_id" name="purok_id" class="form-select">
                        <option value="">Select</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (int)hf('purok_id') === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="classification">Classification</label>
                    <select id="classification" name="classification" class="form-select">
                        <?php foreach (['residential', 'commercial', 'industrial', 'mixed'] as $cl): ?>
                            <option value="<?= $cl ?>" <?= (hf('classification') ?: 'residential') === $cl ? 'selected' : '' ?>><?= ucwords($cl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <?php foreach (['active', 'vacant', 'demolished'] as $st): ?>
                            <option value="<?= $st ?>" <?= (hf('status') ?: 'active') === $st ? 'selected' : '' ?>><?= ucwords($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $submitLabel ?></button>
        <a href="<?= admin_url('households') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a>
    </div>
</form>
