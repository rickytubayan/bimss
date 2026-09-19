<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Create User</h1>
        <p class="page-subtitle mb-0">Add a user account for a barangay official or resident</p>
    </div>
    <a href="<?= admin_url('settings/users') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Users</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= admin_url('settings/users/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?= e(old('first_name')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?= e(old('last_name')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" value="<?= e(old('username')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="8">
                            <div class="form-text">At least 8 characters.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirm">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" id="password_confirm" name="password_confirm" class="form-control" required minlength="8">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="">Select a role…</option>
                                <?php foreach ($roles as $r): ?>
                                    <?php $rk = $r; ?>
                                    <option value="<?= $r ?>" <?= old('role') === $r ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $r)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select" required>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= $s ?>" <?= (old('status') ?: 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Create User</button>
                        <a href="<?= admin_url('settings/users') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>