<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Duty Schedules</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($rows)) ?> schedule<?= count($rows) === 1 ? '' : 's' ?></p>
    </div>
    <a href="<?= admin_url('tanod') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Tanod</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Schedule Duty</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('tanod/schedule/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="schedule_date">Date <span class="text-danger">*</span></label>
                        <input type="date" id="schedule_date" name="schedule_date" class="form-control" value="<?= e(old('schedule_date') ?: date('Y-m-d')) ?>" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="shift_start">Shift Start <span class="text-danger">*</span></label>
                            <input type="time" id="shift_start" name="shift_start" class="form-control" value="<?= e(old('shift_start') ?: '08:00') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="shift_end">Shift End <span class="text-danger">*</span></label>
                            <input type="time" id="shift_end" name="shift_end" class="form-control" value="<?= e(old('shift_end') ?: '20:00') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="assignment_type">Assignment <span class="text-danger">*</span></label>
                        <select id="assignment_type" name="assignment_type" class="form-select" required>
                            <?php foreach (['patrol', 'checkpoint', 'standby', 'event_duty'] as $at): ?>
                                <option value="<?= $at ?>" <?= old('assignment_type') === $at ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $at)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="assigned_members">Assigned Members <span class="text-danger">*</span></label>
                        <select id="assigned_members" name="assigned_members[]" class="form-select" multiple size="<?= max(2, count($tanods)) ?>" required>
                            <?php foreach ($tanods as $t): ?>
                                <option value="<?= e($t['label']) ?>" <?= in_array($t['label'], old('assigned_members', [])) ? 'selected' : '' ?>><?= e($t['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Ctrl/Cmd + click to select multiple.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Schedule</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-calendar-week me-2 text-primary"></i>All Schedules</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $status === '' ? 'active' : '' ?>" href="<?= admin_url('tanod/schedule') ?>">All</a></li>
                    <?php foreach (['active', 'completed', 'cancelled'] as $st): ?>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2 <?= $status === $st ? 'active' : '' ?>" href="<?= admin_url('tanod/schedule?status=' . $st) ?>"><?= ucfirst($st) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Assignment</th>
                            <th>Members</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $s): ?>
                            <?php
                            $aBadge = match ($s['assignment_type']) {
                                'patrol' => 'badge-blue',
                                'checkpoint' => 'badge-yellow',
                                'event_duty' => 'badge-red',
                                default => 'badge-gray',
                            };
                            $stBadge = match ($s['status']) {
                                'active' => 'badge-green',
                                'cancelled' => 'badge-red',
                                default => 'badge-gray',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold small"><?= format_date($s['schedule_date']) ?></td>
                                <td class="text-muted small"><?= date('g:i A', strtotime($s['shift_start'])) ?> – <?= date('g:i A', strtotime($s['shift_end'])) ?></td>
                                <td><span class="badge rounded-pill <?= $aBadge ?>"><?= ucwords(str_replace('_', ' ', $s['assignment_type'])) ?></span></td>
                                <td class="text-muted small"><?= e(implode(', ', json_decode($s['assigned_members'] ?? '[]', true) ?: [])) ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($s['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No schedules yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>