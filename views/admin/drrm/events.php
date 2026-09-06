<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Disaster Events</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($rows)) ?> recorded event<?= count($rows) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= admin_url('drrm') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-lightning-charge me-2 text-primary"></i>All Events</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $type === '' ? 'active' : '' ?>" href="<?= admin_url('drrm/events') ?>">All</a></li>
                    <?php foreach (['flood', 'typhoon', 'earthquake', 'fire', 'landslide', 'volcanic', 'drought', 'other'] as $t): ?>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2 <?= $type === $t ? 'active' : '' ?>" href="<?= admin_url('drrm/events?type=' . $t) ?>"><?= ucfirst($t) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Type</th>
                            <th>Started</th>
                            <th>Severity</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $e): ?>
                            <?php
                            $sevBadge = match ($e['severity']) {
                                'catastrophic' => 'badge-red',
                                'severe' => 'badge-red',
                                'moderate' => 'badge-yellow',
                                default => 'badge-gray',
                            };
                            $typeBadge = in_array($e['type'], ['flood', 'typhoon', 'earthquake', 'volcanic']) ? 'badge-blue' : 'badge-yellow';
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e($e['name']) ?></td>
                                <td><span class="badge rounded-pill <?= $typeBadge ?>"><?= ucfirst($e['type']) ?></span></td>
                                <td class="text-muted small"><?= format_date($e['datetime_start'], 'M j, Y g:i A') ?></td>
                                <td><span class="badge rounded-pill <?= $sevBadge ?>"><?= ucfirst($e['severity']) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= admin_url('drrm/events/' . $e['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No disaster events recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Record Event</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('drrm/events/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="name">Event Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= e(old('name')) ?>" placeholder="e.g. Typhoon Karina" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                        <select id="type" name="type" class="form-select" required>
                            <?php foreach (['flood', 'typhoon', 'earthquake', 'fire', 'landslide', 'volcanic', 'drought', 'other'] as $t): ?>
                                <option value="<?= $t ?>" <?= old('type') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="severity">Severity <span class="text-danger">*</span></label>
                        <select id="severity" name="severity" class="form-select" required>
                            <?php foreach (['minor', 'moderate', 'severe', 'catastrophic'] as $s): ?>
                                <option value="<?= $s ?>" <?= old('severity') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="datetime_start">Start <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="datetime_start" name="datetime_start" class="form-control" value="<?= e(old('datetime_start') ?: date('Y-m-d\TH:i')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="datetime_end">End</label>
                        <input type="datetime-local" id="datetime_end" name="datetime_end" class="form-control" value="<?= e(old('datetime_end')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="2"><?= e(old('description')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Event</button>
                </form>
            </div>
        </div>
    </div>
</div>