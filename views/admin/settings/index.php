<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle mb-0">Barangay information and general system settings</p>
    </div>
    <a href="<?= admin_url('settings/users') ?>" class="btn btn-primary"><i class="bi bi-people me-1"></i>User Management</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-gear me-2 text-primary"></i>General Settings</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('settings/update') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="app_name">System Name</label>
                        <input type="text" id="app_name" name="app_name" class="form-control" value="<?= e($appName) ?>">
                        <div class="form-text">Shown in the browser title bar.</div>
                    </div>
                    <hr>
                    <div class="section-title small text-uppercase fw-semibold text-muted mb-3">Barangay Information</div>
                    <div class="mb-3">
                        <label class="form-label" for="barangay_name">Barangay Name</label>
                        <input type="text" id="barangay_name" name="barangay_name" class="form-control" value="<?= e($barangay['name'] ?? '') ?>" placeholder="e.g. Barangay San Isidro">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="barangay_code">Barangay Code</label>
                            <input type="text" id="barangay_code" name="barangay_code" class="form-control" value="<?= e($barangay['code'] ?? '') ?>" placeholder="e.g. 137404001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="barangay_municipality">Municipality / City</label>
                            <input type="text" id="barangay_municipality" name="barangay_municipality" class="form-control" value="<?= e($barangay['municipality'] ?? '') ?>" placeholder="e.g. Quezon City">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="barangay_province">Province</label>
                            <input type="text" id="barangay_province" name="barangay_province" class="form-control" value="<?= e($barangay['province'] ?? '') ?>" placeholder="e.g. Metro Manila">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="barangay_region">Region</label>
                        <input type="text" id="barangay_region" name="barangay_region" class="form-control" value="<?= e($barangay['region'] ?? '') ?>" placeholder="e.g. NCR">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-people me-2 text-primary"></i>User Management</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Create and manage user accounts for barangay officials and staff. Each account has a role that controls access to admin modules.</p>
                <a href="<?= admin_url('settings/users') ?>" class="btn btn-outline-primary w-100"><i class="bi bi-list-check me-1"></i>Manage Users</a>
            </div>
        </div>
        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-info-circle me-2 text-primary"></i>Note</div>
            <div class="card-body small text-muted">
                The barangay name set here is used as a fallback when linking records (e.g. households, evacuation centers) to the geographic barangay. Settings are saved to <code>config/app.php</code>.
            </div>
        </div>
    </div>
</div>