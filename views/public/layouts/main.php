<!DOCTYPE html>
<html lang="en" data-theme="light" data-font-scale="100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Barangay Information Management System">
    <title><?= e($title ?? 'BIMS') ?> | <?= e($barangayName ?? 'Barangay') ?></title>
    <?php $isAdmin = $isAdmin ?? false; ?>
    <?= CSRF::meta() ?>

    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom styles -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/public-portal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/accessibility.css') ?>" id="accessibility-css">
    <script src="<?= asset('js/accessibility.js') ?>" defer></script>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <?php include VIEWS_PATH . '/components/accessibility-bar.php'; ?>


    <nav class="navbar navbar-expand-lg public-navbar sticky-top" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('') ?>">
                <span class="brand-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-buildings"></i></span>
                <span class="d-flex flex-column lh-1">
                    <span class="fw-bold brand-name"><?= e($barangayName ?? 'Barangay') ?></span>
                    <span class="brand-sub small">BIMS Portal</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar"
                    aria-controls="publicNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="publicNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === url('') || parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === rtrim(url(''), '/') . '/' ? 'active' : '') ?>" href="<?= url('') ?>"><i class="bi bi-house-door me-1"></i><?= t('home') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('public/documents') ?>"><i class="bi bi-file-earmark-text me-1"></i><?= t('services') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('public/appointments') ?>"><i class="bi bi-calendar-check me-1"></i><?= t('appointments') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('public/bulletin') ?>"><i class="bi bi-megaphone me-1"></i><?= t('bulletin') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('public/map') ?>"><i class="bi bi-geo-alt me-1"></i><?= t('map') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('public/transparency') ?>"><i class="bi bi-bar-chart-line me-1"></i><?= t('transparency') ?></a></li>
                    <?php if (\Auth::check()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i><?= t('my_account') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="<?= $isAdmin ? url('admin/dashboard') : url('public/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= url('auth/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i><?= t('logout') ?></a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-primary btn-sm px-3" href="<?= url('auth/login') ?>"><i class="bi bi-box-arrow-in-right me-1"></i><?= t('login') ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div id="tts-reader" class="tts-reader" role="region" aria-live="polite"></div>

    <main id="main-content" class="public-main py-4">
        <div class="container">
            <?php if ($error = flash('error')): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <div><?= e($error) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($success = flash('success')): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div><?= e($success) ?></div>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>

    <footer class="public-footer mt-auto">
        <div class="container py-4">
            <div class="row gy-3">
                <div class="col-md-8">
                    <strong><?= e($barangayName ?? 'Barangay') ?></strong><br>
                    <span class="text-white-50"><?= t('footer_tagline') ?></span>
                </div>
                <div class="col-md-4 d-flex flex-column gap-1 footer-links">
                    <a class="text-white-50 text-decoration-none" href="<?= url('public/bulletin') ?>"><i class="bi bi-megaphone me-1"></i><?= t('bulletin') ?></a>
                    <a class="text-white-50 text-decoration-none" href="<?= url('public/map') ?>"><i class="bi bi-geo-alt me-1"></i><?= t('map') ?></a>
                    <a class="text-white-50 text-decoration-none" href="<?= url('public/transparency') ?>"><i class="bi bi-bar-chart-line me-1"></i><?= t('transparency') ?></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom py-2 text-center">
            <div class="container small text-white-50">&copy; <?= date('Y') ?> <?= e($barangayName ?? 'Barangay') ?> BIMS</div>
        </div>
    </footer>

    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>" defer></script>
    <script src="<?= asset('js/tts.js') ?>" defer></script>
    <script src="<?= asset('js/voice.js') ?>" defer></script>
</body>
</html>
