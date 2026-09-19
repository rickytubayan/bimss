<div class="page-header">
    <h1 class="page-title">Add Appointment</h1>
    <p class="page-subtitle">Book a resident into an available time slot</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= admin_url('appointments/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="resident_id">Resident <span class="text-danger">*</span></label>
                            <select id="resident_id" name="resident_id" class="form-select" required>
                                <option value="">Select resident</option>
                                <?php foreach ($residents as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= old('resident_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['last_name'] . ', ' . $r['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="slot_id">Time Slot <span class="text-danger">*</span></label>
                            <select id="slot_id" name="slot_id" class="form-select" required>
                                <option value="">Select available slot</option>
                                <?php if (empty($slots)): ?>
                                    <option value="" disabled>No open slots. Create a slot first.</option>
                                <?php else: ?>
                                    <?php foreach ($slots as $slot): ?>
                                        <option value="<?= $slot['id'] ?>" <?= old('slot_id') == $slot['id'] ? 'selected' : '' ?>>
                                            <?= e(format_date($slot['slot_date'], 'M d, Y')) ?> — <?= e(date('h:i A', strtotime($slot['time_start'])) . ' - ' . date('h:i A', strtotime($slot['time_end']))) ?>
                                            (<?= e($slot['current_booked']) ?>/<?= e($slot['max_capacity']) ?> booked)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="purpose">Purpose <span class="text-danger">*</span></label>
                            <input type="text" id="purpose" name="purpose" class="form-control" value="<?= e(old('purpose')) ?>" required>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Book Appointment</button>
                        <a href="<?= admin_url('appointments') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>