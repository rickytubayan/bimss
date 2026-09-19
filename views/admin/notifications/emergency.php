<?php
$sevColors = ['low' => 'badge-green', 'medium' => 'badge-yellow', 'high' => 'badge-gray', 'critical' => 'badge-red'];
$sevLabels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];
$statusColors = ['active' => 'badge-red', 'resolved' => 'badge-green', 'expired' => 'badge-gray'];
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Emergency Alert</h1>
        <p class="page-subtitle mb-0">Broadcast a public safety alert to all user accounts</p>
    </div>
    <a href="<?= admin_url('notifications') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Notifications</a>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <?php if ($canSend): ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-broadcast me-2 text-danger"></i>Send Alert</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('notifications/emergency/send') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="alert_type">Alert Type <span class="text-danger">*</span></label>
                        <input type="text" id="alert_type" name="alert_type" class="form-control" value="<?= e(old('alert_type')) ?>" placeholder="e.g. Flood, Fire, Typhoon, Earthquake" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="message">Message <span class="text-danger">*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="4" placeholder="Instructions for residents, contact numbers, evacuation info…" required><?= e(old('message')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="severity">Severity <span class="text-danger">*</span></label>
                        <select id="severity" name="severity" class="form-select" required>
                            <?php foreach ($sevLabels as $v => $lbl): ?>
                                <option value="<?= $v ?>" <?= (old('severity') ?: 'medium') === $v ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Puroks/Sitios</label>
                        <?php if (!empty($puroks)): ?>
                            <div class="border rounded p-2" style="max-height:180px;overflow-y:auto;">
                                <?php foreach ($puroks as $p): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="target_puroks[]" value="<?= $p['id'] ?>"
                                               id="purok-<?= $p['id'] ?>" <?= in_array((string)$p['id'], (array)old('target_puroks')) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="purok-<?= $p['id'] ?>">
                                            <?= e($p['name']) ?> <span class="text-muted small"><?= ucfirst($p['type']) ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="form-text">No puroks/sitios registered yet. The alert will still be broadcast to all users.</div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-danger w-100"><i class="bi bi-broadcast me-1"></i>Send Emergency Alert</button>
                    <div class="form-text text-center mt-2">Notifies every active user account immediately.</div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-shield-lock d-block fs-3 mb-2"></i>
                You do not have permission to send emergency alerts. Contact the Punong Barangay to request access.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Alerts</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Type</th><th>Severity</th><th>Message</th><th>Sent By</th><th>Status</th><th>When</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $a): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($a['alert_type']) ?></td>
                                <td><span class="badge rounded-pill <?= $sevColors[$a['severity']] ?? 'badge-gray' ?>"><?= $sevLabels[$a['severity']] ?? ucfirst($a['severity']) ?></span></td>
                                <td class="small text-muted" style="max-width:220px;"><?= e(truncate($a['message'], 60)) ?></td>
                                <td class="text-muted small"><?= e($a['sent_by_name'] ?? '—') ?></td>
                                <td><span class="badge rounded-pill <?= $statusColors[$a['status']] ?? 'badge-gray' ?>"><?= ucfirst($a['status']) ?></span></td>
                                <td class="text-muted small text-nowrap"><?= time_ago($a['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($alerts)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No emergency alerts sent yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>