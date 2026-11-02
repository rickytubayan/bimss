<?php
$isAdmin = ($user['role'] ?? 'resident') !== 'resident';
$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$initial = strtoupper(mb_substr($user['username'] ?? 'U', 0, 1));
$statusColors = ['active' => 'text-bg-success', 'inactive' => 'text-bg-secondary', 'suspended' => 'text-bg-danger'];
?>
<div class="page-header">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Manage your account details and password.</p>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="avatar rounded-circle d-flex align-items-center justify-content-center fs-3" style="width:64px;height:64px;"><?= e($initial) ?></span>
                    <div class="lh-sm">
                        <h2 class="h5 mb-1"><?= e($fullName ?: $user['username']) ?></h2>
                        <div class="text-muted small">@<?= e($user['username']) ?></div>
                    </div>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted fw-normal">Email</dt>
                    <dd class="col-sm-8"><?= e($user['email']) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Role</dt>
                    <dd class="col-sm-8"><?= e($userModel->getRoleName($user['role'])) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge <?= $statusColors[$user['status']] ?? 'text-bg-secondary' ?>"><?= ucfirst($user['status']) ?></span>
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Member since</dt>
                    <dd class="col-sm-8"><?= format_date($user['created_at']) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Last login</dt>
                    <dd class="col-sm-8 mb-0"><?= $user['last_login_at'] ? time_ago($user['last_login_at']) : '—' ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-shield-lock me-2 text-primary"></i>Change Password
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= url('public/profile/password') ?>" novalidate>
                    <?= CSRF::field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="current_password">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="current_password" name="current_password" class="form-control"
                                   autocomplete="current-password" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new_password">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" id="new_password" name="new_password" class="form-control"
                                   minlength="8" autocomplete="new-password" required>
                        </div>
                        <div class="form-text">At least 8 characters.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="confirm_password">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                                   minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
