<?php $isAdmin = false; ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="display-5" aria-hidden="true"><i class="bi bi-key text-primary"></i></div>
                    <h1 class="h3 mt-2 fw-bold">Forgot Password</h1>
                    <p class="text-muted small mb-0">We can help you regain access to your account.</p>
                </div>

                <div class="alert alert-info d-flex gap-2 small" role="alert">
                    <i class="bi bi-info-circle-fill mt-1"></i>
                    <div>For security, password resets are handled by the barangay office.</div>
                </div>

                <p class="small mb-2">If you can still sign in and want to change your password, open <a href="<?= url('public/profile') ?>">My Profile</a> after logging in.</p>
                <p class="small mb-2">If you have forgotten your password, please:</p>
                <ol class="small text-muted ps-3 mb-4">
                    <li class="mb-1">Visit the barangay hall during office hours.</li>
                    <li class="mb-1">Present a valid ID and the email address registered to your account.</li>
                    <li>Ask the administrator to reset your password.</li>
                </ol>

                <a href="<?= url('auth/login') ?>" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        </div>
    </div>
</div>
