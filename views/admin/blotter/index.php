<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Blotter</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($rows)) ?> blotter entr<?= count($rows) === 1 ? 'y' : 'ies' ?></p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Entries"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Entries</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Open Cases"><?= $stats['open'] ?></div>
            <div class="stat-label">Open Cases</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check2-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Resolved"><?= $stats['resolved'] ?></div>
            <div class="stat-label">Resolved</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-archive"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Closed"><?= $stats['closed'] ?></div>
            <div class="stat-label">Closed</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Register Walk-in Blotter</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('blotter/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="reporter_name">Reporter Name <span class="text-danger">*</span></label>
                        <input type="text" id="reporter_name" name="reporter_name" class="form-control" value="<?= e(old('reporter_name')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reporter_contact">Reporter Contact</label>
                        <input type="text" id="reporter_contact" name="reporter_contact" class="form-control" value="<?= e(old('reporter_contact')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="incident_type">Incident Type <span class="text-danger">*</span></label>
                        <select id="incident_type" name="incident_type" class="form-select" required>
                            <?php foreach (['theft', 'physical_injury', 'vawc', 'fraud', 'quarrel', 'trespassing', 'other'] as $it): ?>
                                <option value="<?= $it ?>" <?= old('incident_type') === $it ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $it)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="narrative">Narrative <span class="text-danger">*</span></label>
                        <textarea id="narrative" name="narrative" class="form-control" rows="3" required><?= e(old('narrative')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-control" value="<?= e(old('location')) ?>">
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
                        <label class="form-label" for="date_time_of_incident">Date &amp; Time of Incident <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="date_time_of_incident" name="date_time_of_incident" class="form-control" value="<?= e(old('date_time_of_incident') ?: date('Y-m-d\TH:i')) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Entry</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-journal-text me-2 text-primary"></i>Blotter Entries</span>
                <ul class="nav nav-pills nav-sm">
                    <?php foreach ($statuses = ['', 'filed', 'investigating', 'for_hearing', 'resolved', 'closed'] as $st): ?>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2 <?= $status === $st ? 'active' : '' ?>" href="<?= admin_url($st === '' ? 'blotter' : 'blotter?status=' . $st) ?>"><?= $st === '' ? 'All' : ucwords(str_replace('_', ' ', $st)) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Case #</th>
                            <th>Reporter</th>
                            <th>Incident</th>
                            <th>Location</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $stBadge = match ($r['status']) {
                                'resolved', 'closed' => 'badge-green',
                                'investigating' => 'badge-blue',
                                'for_hearing' => 'badge-gray',
                                default => 'badge-yellow',
                            };
                            $typeBadge = in_array($r['incident_type'], ['theft', 'physical_injury', 'vawc']) ? 'badge-red' : (in_array($r['incident_type'], ['fraud', 'quarrel', 'trespassing']) ? 'badge-yellow' : 'badge-gray');
                            ?>
                            <tr>
                                <td class="fw-semibold small"><?= e($r['case_number'] ?? '—') ?></td>
                                <td>
                                    <div class="fw-semibold"><?= e($r['reporter_name']) ?></div>
                                    <div class="text-muted small"><?= e($r['report_type'] === 'online' ? 'Online' : 'Walk-in') ?></div>
                                </td>
                                <td>
                                    <div><?= ucwords(str_replace('_', ' ', $r['incident_type'])) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 220px;"><?= e($r['narrative']) ?></div>
                                </td>
                                <td class="text-muted small"><?= e($r['location'] ?? ($r['purok_name'] ?? '—')) ?></td>
                                <td class="text-muted small"><?= format_date($r['date_time_of_incident'], 'M j, Y g:i A') ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords(str_replace('_', ' ', $r['status'])) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= admin_url('blotter/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No blotter entries yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>