<?php $isAdmin = false; ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="display-5" aria-hidden="true"><i class="bi bi-envelope-at text-primary"></i></div>
                    <h1 class="h3 mt-2 fw-bold">Login with OTP</h1>
                    <p class="text-muted small">Enter your email and we'll send you a verification code.</p>
                </div>

                <form method="POST" action="<?= url('auth/otp') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control" required
                                autocomplete="email" value="<?= e(old('email')) ?>" data-read-aloud="Email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="otp">Verification Code</label>
                        <input type="text" id="otp" name="otp" class="form-control form-control-lg text-center letter-spacing-3" required
                            maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="000000" data-read-aloud="Verification code">
                        <div class="form-text">Enter the 6-digit code sent to your email.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Verify</button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= url('auth/login') ?>"><i class="bi bi-arrow-left me-1"></i>Back to login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>.letter-spacing-3 { letter-spacing: 0.5rem; }</style>
