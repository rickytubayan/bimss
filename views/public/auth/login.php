<?php $isAdmin = false; ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="display-5" aria-hidden="true"><i class="bi bi-buildings text-primary"></i></div>
                    <h1 class="h3 mt-2 fw-bold"><?= t('login') ?></h1>
                </div>

                <form method="POST" action="<?= url('auth/login') ?>" novalidate>
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
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control" required
                                autocomplete="current-password" data-read-aloud="Password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i><?= t('login') ?></button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= url('auth/otp') ?>"><i class="bi bi-shield-lock me-1"></i>Login with OTP</a>
                </div>

                <hr class="my-4">

                <div class="text-center">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="<?= url('auth/register') ?>" class="btn btn-outline-primary w-100 mt-2"><i class="bi bi-person-plus me-1"></i><?= t('register') ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
