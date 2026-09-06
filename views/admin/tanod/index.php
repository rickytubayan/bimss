<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Tanod & CCTV</h1>
        <p class="page-subtitle mb-0">Barangay tanod roster, duty schedules & surveillance</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('tanod/schedule') ?>" class="btn btn-outline-primary"><i class="bi bi-calendar-week me-1"></i>Schedules</a>
        <a href="<?= admin_url('tanod/cctv') ?>" class="btn btn-outline-secondary"><i class="bi bi-camera-video me-1"></i>CCTV</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-shield-exclamation"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Tanods"><?= $stats['roster'] ?></div>
            <div class="stat-label">Active Tanods</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-calendar-week"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Schedules"><?= $stats['schedules'] ?></div>
            <div class="stat-label">Total Schedules</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-calendar-check"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="This Week"><?= $stats['this_week'] ?></div>
            <div class="stat-label">Duties This Week</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-shield-check"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Today"><?= $stats['today'] ?></div>
            <div class="stat-label">On Duty Today</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><i class="bi bi-people me-2 text-primary"></i>Tanod Roster (<?= count($roster) ?>)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roster as $t): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($t['last_name'] . ', ' . $t['first_name']) ?></td>
                                <td class="text-muted small"><?= e($t['username']) ?></td>
                                <td><span class="badge rounded-pill <?= $t['status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= ucfirst($t['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($roster)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No tanod members registered.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event me-2 text-primary"></i>Upcoming Duties</span>
                <a href="<?= admin_url('tanod/schedule') ?>" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Assignment</th>
                            <th>Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $s): ?>
                            <?php
                            $aBadge = match ($s['assignment_type']) {
                                'patrol' => 'badge-blue',
                                'checkpoint' => 'badge-yellow',
                                'event_duty' => 'badge-red',
                                default => 'badge-gray',
                            };
                            ?>
                            <tr>
                                <td class="small fw-semibold"><?= format_date($s['schedule_date']) ?></td>
                                <td class="text-muted small"><?= date('g:i A', strtotime($s['shift_start'])) ?> – <?= date('g:i A', strtotime($s['shift_end'])) ?></td>
                                <td><span class="badge rounded-pill <?= $aBadge ?>"><?= ucwords(str_replace('_', ' ', $s['assignment_type'])) ?></span></td>
                                <td class="text-muted small"><?= e(implode(', ', json_decode($s['assigned_members'] ?? '[]', true) ?: [])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($upcoming)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No upcoming duties scheduled.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>