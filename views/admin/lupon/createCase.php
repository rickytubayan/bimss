<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">File New Case</h1>
        <p class="page-subtitle mb-0">Register a dispute for mediation under the Lupon</p>
    </div>
    <a href="<?= admin_url('lupon/cases') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Cases</a>
</div>

<form method="POST" action="<?= admin_url('lupon/cases/store') ?>" novalidate>
    <?= CSRF::field() ?>

    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center"><i class="bi bi-briefcase me-2 text-primary"></i>Case Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="complainant_resident_id">Complainant <span class="text-danger">*</span></label>
                    <select id="complainant_resident_id" name="complainant_resident_id" class="form-select" required>
                        <option value="">Select complainant...</option>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= old('complainant_resident_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="respondent_resident_id">Respondent <span class="text-danger">*</span></label>
                    <select id="respondent_resident_id" name="respondent_resident_id" class="form-select" required>
                        <option value="">Select respondent...</option>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= old('respondent_resident_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="nature_of_dispute">Nature of Dispute <span class="text-danger">*</span></label>
                    <select id="nature_of_dispute" name="nature_of_dispute" class="form-select" required>
                        <option value="civil" <?= old('nature_of_dispute') === 'civil' ? 'selected' : '' ?>>Civil</option>
                        <option value="criminal" <?= old('nature_of_dispute') === 'criminal' ? 'selected' : '' ?>>Criminal</option>
                        <option value="other" <?= old('nature_of_dispute') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="date_filed">Date Filed <span class="text-danger">*</span></label>
                    <input type="date" id="date_filed" name="date_filed" class="form-control" value="<?= e(old('date_filed') ?: date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label" for="cause_of_action">Cause of Action / Complaint <span class="text-danger">*</span></label>
                    <textarea id="cause_of_action" name="cause_of_action" class="form-control" rows="4" required><?= e(old('cause_of_action')) ?></textarea>
                </div>
            </div>
            <p class="form-text mt-3 mb-0">A unique case number (e.g. KP-XXXXXX-123) is generated automatically upon filing.</p>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>File Case</button>
        <a href="<?= admin_url('lupon/cases') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a>
    </div>
</form>