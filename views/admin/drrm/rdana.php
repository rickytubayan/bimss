<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">RDANA Assessment</h1>
        <p class="page-subtitle mb-0">For event: <?= e($event['name']) ?></p>
    </div>
    <a href="<?= admin_url('drrm/events/' . (int)$event['id']) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Event</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><i class="bi bi-clipboard-data me-2 text-primary"></i>Filed Assessments (<?= count($reports) ?>)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event</th>
                            <th>Assessed By</th>
                            <th>Authority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $r): ?>
                            <?php $stBadge = $r['status'] === 'submitted' ? 'badge-blue' : ($r['status'] === 'consolidated' ? 'badge-green' : 'badge-gray'); ?>
                            <tr>
                                <td class="small fw-semibold"><?= format_date($r['assessment_date']) ?></td>
                                <td class="text-muted small"><?= e($r['event_name']) ?></td>
                                <td class="text-muted small"><?= e($r['assessed_by'] ?? '—') ?></td>
                                <td class="text-muted small"><?= e(($r['local_authority_name'] ?? '') !== '' ? $r['local_authority_name'] . ', ' . $r['local_authority_position'] : '—') ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($r['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reports)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No RDANA assessments filed yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <form method="POST" action="<?= admin_url('drrm/rdana/store') ?>" novalidate>
            <?= CSRF::field() ?>
            <input type="hidden" name="disaster_event_id" value="<?= (int)$event['id'] ?>">
            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>File New Assessment</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="assessment_date">Assessment Date <span class="text-danger">*</span></label>
                        <input type="date" id="assessment_date" name="assessment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="assessed_by">Assessed By</label>
                        <input type="text" id="assessed_by" name="assessed_by" class="form-control" value="<?= e(old('assessed_by')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="local_authority_name">Authority Name</label>
                        <input type="text" id="local_authority_name" name="local_authority_name" class="form-control" value="<?= e(old('local_authority_name')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="local_authority_position">Authority Position</label>
                        <input type="text" id="local_authority_position" name="local_authority_position" class="form-control" value="<?= e(old('local_authority_position')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="summary_narrative">Summary Narrative</label>
                        <textarea id="summary_narrative" name="summary_narrative" class="form-control" rows="3"><?= e(old('summary_narrative')) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-3">
                <div class="card-header"><i class="bi bi-house-exclamation me-2 text-primary"></i>Shelter Impact</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="destroyed_count">Destroyed</label>
                            <input type="number" min="0" id="destroyed_count" name="destroyed_count" class="form-control" value="<?= e(old('destroyed_count', 0)) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="damaged_count">Damaged</label>
                            <input type="number" min="0" id="damaged_count" name="damaged_count" class="form-control" value="<?= e(old('damaged_count', 0)) ?>">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="percentage_destroyed">% Destroyed</label>
                            <input type="text" id="percentage_destroyed" name="percentage_destroyed" class="form-control" value="<?= e(old('percentage_destroyed')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="percentage_damaged">% Damaged</label>
                            <input type="text" id="percentage_damaged" name="percentage_damaged" class="form-control" value="<?= e(old('percentage_damaged')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="immediate_needs">Immediate Needs</label>
                        <textarea id="immediate_needs" name="immediate_needs" class="form-control" rows="2"><?= e(old('immediate_needs')) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-3">
                <div class="card-header"><i class="bi bi-people me-2 text-primary"></i>Human Effects</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr><th>Category</th><th class="text-end">Male</th><th class="text-end">Female</th><th class="text-end">Children</th><th class="text-end">Senior</th><th class="text-end">PWD</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (['affected', 'displaced', 'dead', 'injured', 'missing'] as $cat): ?>
                                    <tr>
                                        <td class="text-capitalize"><?= $cat ?></td>
                                        <td class="text-end"><input type="number" min="0" name="effects_<?= $cat ?>_male" class="form-control form-control-sm" style="width:60px" value="0"></td>
                                        <td class="text-end"><input type="number" min="0" name="effects_<?= $cat ?>_female" class="form-control form-control-sm" style="width:60px" value="0"></td>
                                        <td class="text-end"><input type="number" min="0" name="effects_<?= $cat ?>_children" class="form-control form-control-sm" style="width:60px" value="0"></td>
                                        <td class="text-end"><input type="number" min="0" name="effects_<?= $cat ?>_senior" class="form-control form-control-sm" style="width:60px" value="0"></td>
                                        <td class="text-end"><input type="number" min="0" name="effects_<?= $cat ?>_pwd" class="form-control form-control-sm" style="width:60px" value="0"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-text mt-1">Leave rows as zeros to skip recording that category.</div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save RDANA Report</button>
                <a href="<?= admin_url('drrm/events/' . (int)$event['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>