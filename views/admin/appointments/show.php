<?php
$badgeClass = match($appointment['status']) {
    'booked' => 'bg-warning text-dark',
    'completed' => 'bg-success',
    'cancelled' => 'bg-danger',
    'no_show' => 'bg-secondary',
    default => 'bg-secondary',
};
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Appointment #<?= e($appointment['id']) ?></h4>
    <a href="<?= admin_url('appointments') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Appointment Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width:160px">Appointment ID</th>
                        <td>#<?= e($appointment['id']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Purpose</th>
                        <td><?= e($appointment['purpose']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status</th>
                        <td><span class="badge <?= $badgeClass ?>"><?= e(ucfirst($appointment['status'])) ?></span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Queue Number</th>
                        <td><?= $appointment['queue_number'] ? e($appointment['queue_number']) : '<span class="text-muted">Not assigned</span>' ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Slot Date</th>
                        <td><?= e(format_date($appointment['slot_date'], 'l, M d, Y')) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Time</th>
                        <td><?= e(date('h:i A', strtotime($appointment['time_start']))) ?> — <?= e(date('h:i A', strtotime($appointment['time_end']))) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Slot Type</th>
                        <td><span class="badge <?= $appointment['slot_type'] === 'priority' ? 'bg-info' : 'bg-light text-dark' ?>"><?= e(ucfirst($appointment['slot_type'])) ?></span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created At</th>
                        <td><?= e(format_date($appointment['created_at'], 'M d, Y h:i A')) ?></td>
                    </tr>
                </table>

                <?php if ($appointment['status'] === 'booked'): ?>
                    <hr>
                    <div class="d-flex gap-2">
                        <form action="<?= admin_url('appointments/' . $appointment['id'] . '/complete') ?>" method="POST">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Mark as Completed
                            </button>
                        </form>
                        <form action="<?= admin_url('appointments/' . $appointment['id'] . '/cancel') ?>" method="POST">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                <i class="bi bi-x-circle"></i> Cancel Appointment
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Resident Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width:120px">Name</th>
                        <td><?= e($appointment['first_name'] . ' ' . $appointment['last_name']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Email</th>
                        <td><?= e($appointment['email']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Phone</th>
                        <td><?= e($appointment['phone']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Address</th>
                        <td><?= e($appointment['address'] ?? '—') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
