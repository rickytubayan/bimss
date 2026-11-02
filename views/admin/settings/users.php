<?php
$roleColors = [
    'captain' => 'badge-red',
    'kagawad' => 'badge-blue',
    'secretary' => 'badge-yellow',
    'treasurer' => 'badge-yellow',
    'bhw' => 'badge-green',
    'tanod' => 'badge-gray',
    'census' => 'badge-blue',
    'sk_chair' => 'badge-green',
    'resident' => 'badge-gray',
];
$statusColors = ['active' => 'badge-green', 'inactive' => 'badge-gray', 'suspended' => 'badge-red'];
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle mb-0"><?= count($users) ?> user account(s)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('settings') ?>" class="btn btn-outline-secondary"><i class="bi bi-gear me-1"></i>Settings</a>
        <a href="<?= admin_url('settings/users/create') ?>" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>New User</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Accounts"><?= array_sum($roleCounts) ?></div>
            <div class="stat-label">Total Accounts</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-award"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Officials"><?= array_sum($roleCounts) - $roleCounts['resident'] ?></div>
            <div class="stat-label">Barangay Officials</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-person-badge"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Residents"><?= $roleCounts['resident'] ?></div>
            <div class="stat-label">Resident Accounts</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-shield-check"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Distinct Roles"><?= count(array_filter($roleCounts, fn($c) => $c > 0)) ?></div>
            <div class="stat-label">Roles in Use</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <form method="GET" action="<?= admin_url('settings/users') ?>" class="row g-2">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search name, username, or email..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All roles</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r ?>" <?= $role === $r ? 'selected' : '' ?>><?= e($userModel->getRoleName($r)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Created</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar rounded-circle d-flex align-items-center justify-content-center"><?= e(strtoupper(mb_substr($u['username'] ?? 'U', 0, 1))) ?></span>
                                <div class="lh-1">
                                    <div class="fw-semibold"><?= e($u['username']) ?></div>
                                    <div class="text-muted small"><?= e(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?: '—' ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted"><?= e($u['email']) ?></td>
                        <td><span class="badge rounded-pill <?= $roleColors[$u['role']] ?? 'badge-gray' ?>"><?= e($userModel->getRoleName($u['role'])) ?></span></td>
                        <td><span class="badge rounded-pill <?= $statusColors[$u['status']] ?? 'badge-gray' ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td class="text-muted small text-nowrap"><?= $u['last_login_at'] ? time_ago($u['last_login_at']) : '—' ?></td>
                        <td class="text-muted small text-nowrap"><?= format_date($u['created_at']) ?></td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#resetPassword<?= (int)$u['id'] ?>" title="Reset password">
                                <i class="bi bi-key me-1"></i>Reset Password
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No user accounts found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach ($users as $u): $uid = (int)$u['id']; ?>
    <div class="modal fade" id="resetPassword<?= $uid ?>" tabindex="-1" aria-labelledby="resetPasswordLabel<?= $uid ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= admin_url('settings/users/' . $uid . '/reset-password') ?>">
                    <?= CSRF::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetPasswordLabel<?= $uid ?>"><i class="bi bi-key me-2 text-primary"></i>Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Set a new password for <strong><?= e($u['username']) ?></strong> (<?= e($u['email']) ?>).
                        </p>
                        <div class="mb-3">
                            <label class="form-label" for="new_password_<?= $uid ?>">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="new_password_<?= $uid ?>" name="new_password"
                                   minlength="8" autocomplete="new-password" required>
                            <div class="form-text">At least 8 characters.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="confirm_password_<?= $uid ?>">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password_<?= $uid ?>" name="confirm_password"
                                   minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>