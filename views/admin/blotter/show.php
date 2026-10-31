<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Blotter Entry #<?= (int)$row['id'] ?></h1>
        <p class="page-subtitle mb-0"><?= e($row['case_number'] ?? 'No case number yet') ?></p>
    </div>
    <a href="<?= admin_url('blotter') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Blotter</a>
</div>

<?php
$stBadge = match ($row['status']) {
    'resolved', 'closed' => 'badge-green',
    'investigating' => 'badge-blue',
    'for_hearing' => 'badge-gray',
    default => 'badge-yellow',
};
$typeBadge = in_array($row['incident_type'], ['theft', 'physical_injury', 'vawc']) ? 'badge-red' : (in_array($row['incident_type'], ['fraud', 'quarrel', 'trespassing']) ? 'badge-yellow' : 'badge-gray');
?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Incident Details</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge rounded-pill <?= $typeBadge ?>"><?= ucwords(str_replace('_', ' ', $row['incident_type'])) ?></span>
                    <span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $row['status'])) ?></span>
                    <span class="badge rounded-pill badge-blue"><?= $row['report_type'] === 'online' ? 'Online Report' : 'Walk-in' ?></span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Reporter</div>
                        <div class="fw-semibold"><?= e($row['reporter_name']) ?></div>
                        <div class="text-muted small"><?= e($row['reporter_contact'] ?? 'No contact') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Date &amp; Time of Incident</div>
                        <div class="fw-semibold"><?= format_date($row['date_time_of_incident'], 'M j, Y g:i A') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Location</div>
                        <div><?= e($row['location'] ?? '—') ?></div>
                        <div class="text-muted small"><?= e($row['purok_name'] ?? 'No purok') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Assigned Tanod</div>
                        <div><?= e($row['tanod_name'] ?? 'Unassigned') ?></div>
                    </div>
                </div>
                <hr>
                <div class="text-muted small fw-semibold text-uppercase mb-1">Narrative</div>
                <p class="mb-0" style="white-space: pre-line;"><?= e($row['narrative']) ?></p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-people me-2 text-primary"></i>Witnesses (<?= count($witnesses) ?>)</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addWitnessModal">
                    <i class="bi bi-person-plus me-1"></i>Add Witness
                </button>
            </div>
            <?php if (empty($witnesses)): ?>
                <div class="alert alert-warning border-0 rounded-0 mb-0 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    No witnesses recorded yet. A blotter report should have at least one witness &mdash; add one using the button above.
                </div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Statement</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($witnesses as $w): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($w['name']) ?></td>
                                <td class="text-muted"><?= e($w['contact'] ?? '—') ?></td>
                                <td class="text-muted small" style="white-space: pre-line;"><?= e($w['statement'] ?? '—') ?></td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editWitness<?= (int)$w['id'] ?>" title="Edit witness">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="<?= admin_url('blotter/' . (int)$row['id'] . '/witnesses/' . (int)$w['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Remove this witness from the blotter entry?');">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete witness"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($witnesses)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No witnesses recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-gear me-2 text-primary"></i>Update Case</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('blotter/' . (int)$row['id'] . '/update') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st ?>" <?= $row['status'] === $st ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $st)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="tanod_assigned">Assign Tanod</label>
                        <select id="tanod_assigned" name="tanod_assigned" class="form-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= (int)$row['tanod_assigned'] === (int)$u['id'] ? 'selected' : '' ?>><?= e($u['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="case_number">Case Number</label>
                        <input type="text" id="case_number" name="case_number" class="form-control" value="<?= e($row['case_number'] ?? '') ?>" placeholder="Auto-assigned if blank">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Update Entry</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Witness Modal -->
<div class="modal fade" id="addWitnessModal" tabindex="-1" aria-labelledby="addWitnessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= admin_url('blotter/' . (int)$row['id'] . '/witnesses/store') ?>">
                <?= CSRF::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addWitnessModalLabel"><i class="bi bi-person-plus me-2 text-primary"></i>Add Witness</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="witness_name">Name <span class="text-danger">*</span></label>
                        <input type="text" id="witness_name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="witness_contact">Contact</label>
                        <input type="text" id="witness_contact" name="contact" class="form-control" maxlength="20">
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="witness_statement">Statement</label>
                        <textarea id="witness_statement" name="statement" class="form-control" rows="3" placeholder="What did the witness see or hear?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Add Witness</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($witnesses as $w): ?>
    <!-- Edit Witness Modal -->
    <div class="modal fade" id="editWitness<?= (int)$w['id'] ?>" tabindex="-1" aria-labelledby="editWitnessLabel<?= (int)$w['id'] ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= admin_url('blotter/' . (int)$row['id'] . '/witnesses/' . (int)$w['id'] . '/update') ?>">
                    <?= CSRF::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="editWitnessLabel<?= (int)$w['id'] ?>"><i class="bi bi-pencil me-2 text-primary"></i>Edit Witness</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="witness_name_<?= (int)$w['id'] ?>">Name <span class="text-danger">*</span></label>
                            <input type="text" id="witness_name_<?= (int)$w['id'] ?>" name="name" class="form-control" value="<?= e($w['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="witness_contact_<?= (int)$w['id'] ?>">Contact</label>
                            <input type="text" id="witness_contact_<?= (int)$w['id'] ?>" name="contact" class="form-control" value="<?= e($w['contact'] ?? '') ?>" maxlength="20">
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="witness_statement_<?= (int)$w['id'] ?>">Statement</label>
                            <textarea id="witness_statement_<?= (int)$w['id'] ?>" name="statement" class="form-control" rows="3"><?= e($w['statement'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
