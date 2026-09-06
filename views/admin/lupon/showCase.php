<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title"><?= e($row['case_number']) ?></h1>
        <p class="page-subtitle mb-0">Filed <?= format_date($row['date_filed']) ?></p>
    </div>
    <a href="<?= admin_url('lupon/cases') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Cases</a>
</div>

<?php
$stBadge = match ($row['status']) {
    'pending_mediation' => 'badge-yellow',
    'pending_pangkat' => 'badge-blue',
    'pending_conciliation' => 'badge-yellow',
    'settled', 'cfa_issued', 'executed' => 'badge-green',
    default => 'badge-red',
};
$natureBadge = $row['nature_of_dispute'] === 'criminal' ? 'badge-red' : ($row['nature_of_dispute'] === 'civil' ? 'badge-blue' : 'badge-gray');
?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><i class="bi bi-briefcase me-2 text-primary"></i>Case Details</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge rounded-pill <?= $natureBadge ?>"><?= ucfirst($row['nature_of_dispute']) ?> dispute</span>
                    <span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $row['status'])) ?></span>
                    <?php if ($row['cfa_number']): ?>
                        <span class="badge rounded-pill badge-green">CFA: <?= e($row['cfa_number']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Complainant</div>
                        <div class="fw-semibold"><?= e($row['complainant_name']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Respondent</div>
                        <div class="fw-semibold"><?= e($row['respondent_name']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Mediation</div>
                        <div><?= $row['date_mediation'] ? format_date($row['date_mediation']) : 'Not scheduled' ?></div>
                        <div class="text-muted small"><?= $row['mediation_outcome'] ? ucwords(str_replace('_', ' ', $row['mediation_outcome'])) : '' ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold text-uppercase">Pangkat / Conciliation</div>
                        <div><?= $row['date_pangkat'] ? format_date($row['date_pangkat']) : 'Not scheduled' ?></div>
                    </div>
                </div>
                <hr>
                <div class="text-muted small fw-semibold text-uppercase mb-1">Cause of Action</div>
                <p class="mb-0" style="white-space: pre-line;"><?= e($row['cause_of_action']) ?></p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header"><i class="bi bi-calendar-event me-2 text-primary"></i>Hearings (<?= count($hearings) ?>)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Scheduled</th>
                            <th>Outcome</th>
                            <th>Notes</th>
                            <th>Next Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hearings as $h): ?>
                            <tr>
                                <td><span class="text-capitalize"><?= e($h['hearing_type']) ?></span></td>
                                <td class="text-muted small"><?= format_date($h['scheduled_date'], 'M j, Y g:i A') ?></td>
                                <td class="text-muted"><?= e($h['outcome'] ? ucwords(str_replace('_', ' ', $h['outcome'])) : '—') ?></td>
                                <td class="text-muted small"><?= e($h['notes'] ?? '—') ?></td>
                                <td class="text-muted small"><?= $h['next_schedule'] ? format_date($h['next_schedule'], 'M j, Y g:i A') : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($hearings)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No hearings scheduled yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-cash-coin me-2 text-primary"></i>Settlements & CFA</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Signatures</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($settlements as $s): ?>
                            <tr>
                                <td><span class="text-capitalize"><?= e(str_replace('_', ' ', $s['type'])) ?></span></td>
                                <td class="text-muted small"><?= format_date($s['settlement_date']) ?></td>
                                <td class="text-muted small" style="white-space: pre-line;"><?= e($s['details']) ?></td>
                                <td class="small">
                                    <span class="badge rounded-pill <?= $s['signed_by_complainant'] ? 'badge-green' : 'badge-gray' ?>">C<?= $s['signed_by_complainant'] ? '✓' : '—' ?></span>
                                    <span class="badge rounded-pill <?= $s['signed_by_respondent'] ? 'badge-green' : 'badge-gray' ?>">R<?= $s['signed_by_respondent'] ? '✓' : '—' ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($settlements)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No settlements recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($cfaRecords)): ?>
                <div class="card-body border-top">
                    <div class="text-muted small fw-semibold text-uppercase mb-2">Certificate of Arbitration</div>
                    <?php foreach ($cfaRecords as $cfa): ?>
                        <div class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2">
                            <div>
                                <div class="fw-semibold"><?= e($cfa['certificate_number']) ?></div>
                                <div class="text-muted small">Issued <?= format_date($cfa['issued_date']) ?> · Valid until <?= format_date($cfa['valid_until']) ?></div>
                            </div>
                            <span class="badge rounded-pill <?= $cfa['status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= ucfirst($cfa['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><i class="bi bi-calendar-plus me-2 text-primary"></i>Schedule / Record Hearing</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('lupon/cases/' . (int)$row['id'] . '/hearing') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="hearing_type">Type <span class="text-danger">*</span></label>
                        <select id="hearing_type" name="hearing_type" class="form-select" required>
                            <option value="mediation">Mediation</option>
                            <option value="conciliation">Conciliation</option>
                            <option value="arbitration">Arbitration</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="scheduled_date">Scheduled Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="scheduled_date" name="scheduled_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="outcome">Outcome</label>
                        <select id="outcome" name="outcome" class="form-select">
                            <option value="">—</option>
                            <?php foreach (['settled', 'failed', 'adjourned', 'no_show_complainant', 'no_show_respondent'] as $o): ?>
                                <option value="<?= $o ?>"><?= ucwords(str_replace('_', ' ', $o)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="next_schedule">Next Schedule</label>
                        <input type="datetime-local" id="next_schedule" name="next_schedule" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Hearing</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header"><i class="bi bi-handshake me-2 text-primary"></i>Record Settlement</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('lupon/cases/' . (int)$row['id'] . '/settle') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="stype">Type <span class="text-danger">*</span></label>
                        <select id="stype" name="type" class="form-select" required>
                            <option value="amicable_settlement">Amicable Settlement</option>
                            <option value="arbitration_award">Arbitration Award</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="settlement_date">Settlement Date <span class="text-danger">*</span></label>
                        <input type="date" id="settlement_date" name="settlement_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="details">Details <span class="text-danger">*</span></label>
                        <textarea id="details" name="details" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="attested_by">Attested By</label>
                        <select id="attested_by" name="attested_by" class="form-select">
                            <option value="">—</option>
                            <?php foreach ($residents as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= e($r['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="signed_by_complainant" id="signed_by_complainant" value="1">
                        <label class="form-check-label" for="signed_by_complainant">Signed by complainant</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="signed_by_respondent" id="signed_by_respondent" value="1">
                        <label class="form-check-label" for="signed_by_respondent">Signed by respondent</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-check2-circle me-1"></i>Mark Settled</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-patch-check me-2 text-primary"></i>Issue Certificate</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('lupon/cases/' . (int)$row['id'] . '/cfa') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="issued_date">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" id="issued_date" name="issued_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="valid_until">Valid Until <span class="text-danger">*</span></label>
                        <input type="date" id="valid_until" name="valid_until" class="form-control" value="<?= date('Y-m-d', strtotime('+90 days')) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-patch-check me-1"></i>Issue CFA</button>
                </form>
            </div>
        </div>
    </div>
</div>