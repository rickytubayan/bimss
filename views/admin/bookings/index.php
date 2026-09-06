<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Venue Bookings</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($bookings)) ?> booking<?= count($bookings) === 1 ? '' : 's' ?></p>
    </div>
    <a href="#newBooking" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>New Booking</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-calendar-event"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Bookings"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Pending"><?= $stats['pending'] ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Confirmed"><?= $stats['confirmed'] ?></div>
            <div class="stat-label">Confirmed</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-piggy-bank"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Collected Fees">₱<?= number_format($stats['revenue'], 2) ?></div>
            <div class="stat-label">Collected Fees</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4" id="newBooking">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>New Booking</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('bookings/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="venue_name">Venue <span class="text-danger">*</span></label>
                        <input type="text" id="venue_name" name="venue_name" class="form-control" value="<?= e(old('venue_name')) ?>" placeholder="e.g. Covered Court" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="booker_resident_id">Booker Resident <span class="text-danger">*</span></label>
                        <select id="booker_resident_id" name="booker_resident_id" class="form-select" required>
                            <option value="">Select a resident…</option>
                            <?php foreach ($residents as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= old('booker_resident_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="event_date">Event Date <span class="text-danger">*</span></label>
                        <input type="date" id="event_date" name="event_date" class="form-control" value="<?= e(old('event_date')) ?>" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="time_start">Start <span class="text-danger">*</span></label>
                            <input type="time" id="time_start" name="time_start" class="form-control" value="<?= e(old('time_start')) ?>" required>
                        </div>
                        <div class="col">
                            <label class="form-label" for="time_end">End</label>
                            <input type="time" id="time_end" name="time_end" class="form-control" value="<?= e(old('time_end')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="purpose">Purpose <span class="text-danger">*</span></label>
                        <input type="text" id="purpose" name="purpose" class="form-control" value="<?= e(old('purpose')) ?>" placeholder="e.g. Kasal, Evento" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="amount">Amount (₱)</label>
                            <input type="number" step="any" min="0" id="amount" name="amount" class="form-control" value="<?= e(old('amount')) ?>" placeholder="0.00">
                        </div>
                        <div class="col">
                            <label class="form-label" for="payment_status">Payment <span class="text-danger">*</span></label>
                            <select id="payment_status" name="payment_status" class="form-select" required>
                                <?php foreach (['unpaid', 'paid', 'refunded'] as $p): ?>
                                    <option value="<?= $p ?>" <?= old('payment_status') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Booking</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-calendar-event me-2 text-primary"></i>All Bookings</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $status === '' ? 'active' : '' ?>" href="<?= admin_url('bookings') ?>">All</a></li>
                    <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s): ?>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2 <?= $status === $s ? 'active' : '' ?>" href="<?= admin_url('bookings?status=' . $s) ?>"><?= ucfirst($s) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Venue</th>
                    <th>Booker</th>
                    <th>Schedule</th>
                    <th>Purpose</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <?php
                    $statusBadge = match ($b['status']) {
                        'confirmed', 'completed' => 'badge-green',
                        'pending' => 'badge-yellow',
                        default => 'badge-red',
                    };
                    $payBadge = match ($b['payment_status']) {
                        'paid' => 'badge-green',
                        'unpaid' => 'badge-red',
                        default => 'badge-gray',
                    };
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($b['venue_name']) ?></td>
                        <td class="text-muted small"><?= e($b['booker_name']) ?></td>
                        <td class="text-muted small">
                            <div><?= format_date($b['event_date'], 'M j, Y') ?></div>
                            <div><?= format_date($b['time_start'], 'g:i A') ?> – <?= format_date($b['time_end'], 'g:i A') ?></div>
                        </td>
                        <td class="small"><?= e($b['purpose']) ?></td>
                        <td>
                            <span class="badge rounded-pill <?= $payBadge ?>"><?= ucfirst($b['payment_status']) ?></span>
                            <div class="text-muted small mt-1">₱<?= number_format($b['amount'], 2) ?></div>
                        </td>
                        <td><span class="badge rounded-pill <?= $statusBadge ?>"><?= ucfirst($b['status']) ?></span></td>
                        <td class="text-end">
                            <?php if ($b['status'] === 'pending'): ?>
                                <form method="POST" action="<?= admin_url('bookings/confirm/' . $b['id']) ?>" class="d-inline">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg me-1"></i>Confirm</button>
                                </form>
                            <?php endif; ?>
                            <?php if (in_array($b['status'], ['pending', 'confirmed'])): ?>
                                <form method="POST" action="<?= admin_url('bookings/cancel/' . $b['id']) ?>" class="d-inline" onsubmit="return confirm('Cancel this booking?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No venue bookings yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
        </div>
    </div>
</div>