<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Appointments</h4>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="mb-3">
            <a href="<?= admin_url('appointments') ?>" class="btn btn-sm <?= empty($currentStatus) ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
            <a href="<?= admin_url('appointments?status=booked') ?>" class="btn btn-sm <?= $currentStatus === 'booked' ? 'btn-warning' : 'btn-outline-warning' ?>">Booked</a>
            <a href="<?= admin_url('appointments?status=completed') ?>" class="btn btn-sm <?= $currentStatus === 'completed' ? 'btn-success' : 'btn-outline-success' ?>">Completed</a>
            <a href="<?= admin_url('appointments?status=cancelled') ?>" class="btn btn-sm <?= $currentStatus === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' ?>">Cancelled</a>
            <a href="<?= admin_url('appointments?status=no_show') ?>" class="btn btn-sm <?= $currentStatus === 'no_show' ? 'btn-secondary' : 'btn-outline-secondary' ?>">No Show</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Resident</th>
                        <th>Slot Date</th>
                        <th>Time</th>
                        <th>Purpose</th>
                        <th>Queue #</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No appointments found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td><?= e($apt['id']) ?></td>
                                <td><?= e($apt['last_name'] . ', ' . $apt['first_name']) ?></td>
                                <td><?= e(format_date($apt['slot_date'], 'M d, Y')) ?></td>
                                <td><?= e(date('h:i A', strtotime($apt['time_start'])) . ' - ' . date('h:i A', strtotime($apt['time_end']))) ?></td>
                                <td><?= e($apt['purpose']) ?></td>
                                <td><?= $apt['queue_number'] ? e($apt['queue_number']) : '<span class="text-muted">—</span>' ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($apt['status']) {
                                        'booked' => 'bg-warning text-dark',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        'no_show' => 'bg-secondary',
                                        default => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= e(ucfirst($apt['status'])) ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= admin_url('appointments/' . $apt['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if ($apt['status'] === 'booked'): ?>
                                        <form action="<?= admin_url('appointments/' . $apt['id'] . '/complete') ?>" method="POST" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check-circle"></i> Complete
                                            </button>
                                        </form>
                                        <form action="<?= admin_url('appointments/' . $apt['id'] . '/cancel') ?>" method="POST" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
                    if (!empty($currentStatus)) {
                        $queryParams['status'] = $currentStatus;
                    }
                    $baseUrl = admin_url('appointments') . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');

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
