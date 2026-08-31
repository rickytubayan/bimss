<?php
$adminUser = \Auth::user();
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$userModel = new \Models\User();
function admin_nav_active($uri, $prefix) { return str_contains($uri, $prefix) ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="light" data-font-scale="100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?> | BIMS Admin</title>
    <?= CSRF::meta() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/accessibility.css') ?>" id="accessibility-css">
    <script src="<?= asset('js/accessibility.js') ?>" defer></script>
</head>
<body>

<div class="wrapper d-flex">

    <!-- Offcanvas sidebar -->
    <div class="offcanvas offcanvas-start admin-offcanvas" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
        <div class="offcanvas-header admin-sidebar-head">
            <h5 class="offcanvas-title fw-bold text-white d-flex align-items-center gap-2" id="adminSidebarLabel">
                <span class="brand-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-buildings"></i></span>
                <span class="d-flex flex-column lh-1">
                    <span class="fw-bold">BIMS</span>
                    <span class="small text-white-50">Admin Panel</span>
                </span>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 admin-sidebar-body">
            <div class="nav flex-column nav-pills admin-sidebar-nav">
                <div class="sidebar-section">Overview</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/admin/dashboard') ?: (rtrim($currentUri,'/')==='/admin' ? 'active':'') ?>" href="<?= admin_url('dashboard') ?>"><i class="bi bi-speedometer2 sidebar-icon"></i> Dashboard</a>

                <div class="sidebar-section">Population</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/residents') ?>" href="<?= admin_url('residents') ?>"><i class="bi bi-people sidebar-icon"></i> Residents</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/households') ?>" href="<?= admin_url('households') ?>"><i class="bi bi-house sidebar-icon"></i> Households</a>

                <div class="sidebar-section">Services</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/clearance') ?>" href="<?= admin_url('clearances') ?>"><i class="bi bi-file-earmark-text sidebar-icon"></i> Clearances</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/certificates') ?>" href="<?= admin_url('certificates') ?>"><i class="bi bi-patch-check sidebar-icon"></i> Certificates</a>
                <a class="nav-link" href="<?= admin_url('appointments') ?>"><i class="bi bi-calendar-check sidebar-icon"></i> Appointments</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/bulletins') ?>" href="<?= admin_url('bulletins') ?>"><i class="bi bi-megaphone sidebar-icon"></i> Bulletins</a>

                <div class="sidebar-section">Finance</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/finance') ?>" href="<?= admin_url('finance') ?>"><i class="bi bi-cash-coin sidebar-icon"></i> Income & Expenses</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/budget') ?>" href="<?= admin_url('budget') ?>"><i class="bi bi-clipboard-data sidebar-icon"></i> Budget</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/tax') ?>" href="<?= admin_url('tax') ?>"><i class="bi bi-bank sidebar-icon"></i> Tax Ledger</a>

                <div class="sidebar-section">Health & Welfare</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/health') ?>" href="<?= admin_url('health') ?>"><i class="bi bi-heart-pulse sidebar-icon"></i> Health</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/seniors') ?>" href="<?= admin_url('seniors') ?>"><i class="bi bi-person-wheelchair sidebar-icon"></i> Senior & PWD</a>

                <div class="sidebar-section">Peace & Order</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/blotter') ?>" href="<?= admin_url('blotter') ?>"><i class="bi bi-journal-text sidebar-icon"></i> Blotter</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/lupon') ?>" href="<?= admin_url('lupon') ?>"><i class="bi bi-shield-shaded sidebar-icon"></i> Lupon / KP</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/tanod') ?>" href="<?= admin_url('tanod') ?>"><i class="bi bi-shield-exclamation sidebar-icon"></i> Tanod & CCTV</a>

                <div class="sidebar-section">Disaster</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/drrm') ?>" href="<?= admin_url('drrm') ?>"><i class="bi bi-tsunami sidebar-icon"></i> DRRM</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/evacuation') ?>" href="<?= admin_url('evacuation') ?>"><i class="bi bi-signpost-2 sidebar-icon"></i> Evacuation</a>

                <div class="sidebar-section">Assets & Livelihood</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/assets') ?>" href="<?= admin_url('assets') ?>"><i class="bi bi-tools sidebar-icon"></i> Assets</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/bookings') ?>" href="<?= admin_url('bookings') ?>"><i class="bi bi-building sidebar-icon"></i> Venue Bookings</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/livelihood') ?>" href="<?= admin_url('livelihood') ?>"><i class="bi bi-briefcase sidebar-icon"></i> Livelihood</a>

                <div class="sidebar-section">Governance</div>
                <a class="nav-link <?= admin_nav_active($currentUri, '/compliance') ?>" href="<?= admin_url('compliance') ?>"><i class="bi bi-patch-check sidebar-icon"></i> Compliance</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/reports') ?>" href="<?= admin_url('reports') ?>"><i class="bi bi-graph-up sidebar-icon"></i> Reports</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/notification') ?>" href="<?= admin_url('notifications') ?>"><i class="bi bi-bell sidebar-icon"></i> Notifications</a>
                <a class="nav-link <?= admin_nav_active($currentUri, '/settings') ?>" href="<?= admin_url('settings') ?>"><i class="bi bi-gear sidebar-icon"></i> Settings</a>
            </div>
        </div>
        <div class="admin-sidebar-foot small text-white-50 px-3 py-2">BIMS v1.0.0</div>
    </div>

    <!-- Main area -->
    <div class="content flex-grow-1 d-flex flex-column">
        <header class="admin-topbar d-flex align-items-center justify-content-between px-3 py-2">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
                <button class="btn btn-outline-secondary d-none d-lg-inline-flex" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-label="Toggle menu"><i class="bi bi-layout-sidebar"></i></button>
                <strong class="h5 mb-0"><?= e($title ?? 'Dashboard') ?></strong>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= url('') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye me-1"></i>Public Site</a>
                <a href="<?= admin_url('notifications') ?>" class="btn btn-outline-secondary btn-sm position-relative" aria-label="Notifications"><i class="bi bi-bell"></i></a>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar rounded-circle d-flex align-items-center justify-content-center"><?= e(strtoupper(mb_substr($adminUser['username'] ?? 'U', 0, 1))) ?></span>
                        <span class="d-none d-md-flex flex-column lh-1 text-start">
                            <span class="small fw-semibold"><?= e($adminUser['username'] ?? 'Admin') ?></span>
                            <span class="small text-muted"><?= e($userModel->getRoleName($adminUser['role'])) ?></span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="<?= url('') ?>"><i class="bi bi-eye me-2"></i>Public Site</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('auth/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i><?= t('logout') ?></a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="admin-content flex-grow-1 p-3">
            <?php if ($error = flash('error')): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert"><i class="bi bi-exclamation-circle-fill me-2"></i><div><?= e($error) ?></div></div>
            <?php endif; ?>
            <?php if ($success = flash('success')): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert"><i class="bi bi-check-circle-fill me-2"></i><div><?= e($success) ?></div></div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>" defer></script>
<script src="<?= asset('js/tts.js') ?>" defer></script>
</body>
</html>
