<?php $isAdmin = false; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="display-5" aria-hidden="true"><i class="bi bi-person-plus text-primary"></i></div>
                    <h1 class="h3 mt-2 fw-bold"><?= t('register') ?></h1>
                    <p class="text-muted small">Create your citizen account</p>
                </div>

                <form method="POST" action="<?= url('auth/register') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label" for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control"
                                value="<?= e(old('first_name')) ?>" data-read-aloud="First name">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control"
                                value="<?= e(old('last_name')) ?>" data-read-aloud="Last name">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required
                                value="<?= e(old('username')) ?>" data-read-aloud="Username">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                value="<?= e(old('email')) ?>" data-read-aloud="Email">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required
                                minlength="8" autocomplete="new-password" data-read-aloud="Password">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required
                                autocomplete="new-password" data-read-aloud="Confirm password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 mt-4"><i class="bi bi-check-circle me-1"></i><?= t('register') ?></button>
                </form>

                <div class="text-center mt-3">
                    <span class="text-muted">Already have an account?</span>
                    <a href="<?= url('auth/login') ?>"><?= t('login') ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
