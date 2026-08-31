<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Appointment Slots</h4>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Create New Slot</h5>
    </div>
    <div class="card-body">
        <form action="<?= admin_url('appointments/slots/store') ?>" method="POST">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="slot_date" class="form-label">Date</label>
                    <input type="date" id="slot_date" name="slot_date" class="form-control" value="<?= e(old('slot_date')) ?>" required>
                </div>
                <div class="col-md-2">
                    <label for="time_start" class="form-label">Start Time</label>
                    <input type="time" id="time_start" name="time_start" class="form-control" value="<?= e(old('time_start')) ?>" required>
                </div>
                <div class="col-md-2">
                    <label for="time_end" class="form-label">End Time</label>
                    <input type="time" id="time_end" name="time_end" class="form-control" value="<?= e(old('time_end')) ?>" required>
                </div>
                <div class="col-md-2">
                    <label for="max_capacity" class="form-label">Max Capacity</label>
                    <input type="number" id="max_capacity" name="max_capacity" class="form-control" value="<?= e(old('max_capacity', '10')) ?>" min="1" required>
                </div>
                <div class="col-md-2">
                    <label for="slot_type" class="form-label">Type</label>
                    <select id="slot_type" name="slot_type" class="form-select">
                        <option value="regular" <?= old('slot_type') === 'regular' ? 'selected' : '' ?>>Regular</option>
                        <option value="priority" <?= old('slot_type') === 'priority' ? 'selected' : '' ?>>Priority</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> Create Slot
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="mb-3">
            <form method="GET" action="<?= admin_url('appointments/slots') ?>" class="d-inline-flex align-items-center gap-2">
                <label class="form-label mb-0">Filter by date:</label>
                <input type="date" name="date" class="form-control form-control-sm" style="width:auto" value="<?= e($currentDate) ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <?php if ($currentDate): ?>
                    <a href="<?= admin_url('appointments/slots') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Capacity</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($slots)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No appointment slots found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($slots as $slot): ?>
                            <tr>
                                <td><?= e($slot['id']) ?></td>
                                <td><?= e(format_date($slot['slot_date'], 'M d, Y')) ?></td>
                                <td><?= e(date('h:i A', strtotime($slot['time_start']))) ?> — <?= e(date('h:i A', strtotime($slot['time_end']))) ?></td>
                                <td>
                                    <?php
                                    $capacityPercent = $slot['max_capacity'] > 0
                                        ? round(($slot['current_booked'] / $slot['max_capacity']) * 100)
                                        : 0;
                                    $barClass = $capacityPercent >= 100 ? 'bg-danger' : ($capacityPercent >= 75 ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 20px; min-width: 80px;">
                                            <div class="progress-bar <?= $barClass ?>" style="width: <?= $capacityPercent ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= e($slot['current_booked']) ?>/<?= e($slot['max_capacity']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $slot['slot_type'] === 'priority' ? 'bg-info' : 'bg-light text-dark' ?>">
                                        <?= e(ucfirst($slot['slot_type'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $slotStatusClass = match($slot['status']) {
                                        'open' => 'bg-success',
                                        'full' => 'bg-warning text-dark',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $slotStatusClass ?>"><?= e(ucfirst($slot['status'])) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($pagination['total']) && $pagination['total'] > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <?php
                    $queryParams = [];
                    if (!empty($currentDate)) {
                        $queryParams['date'] = $currentDate;
                    }
                    $baseUrl = admin_url('appointments/slots') . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');

                    for ($i = 1; $i <= $pagination['total']; $i++):
                        $pageUrl = $i === 1
                            ? rtrim($baseUrl, '?')
                            : $baseUrl . (empty($queryParams) ? '?' : '&') . 'page=' . $i;
                    ?>
                        <li class="page-item <?= ($i == ($pagination['current'] ?? 1)) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $pageUrl ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
