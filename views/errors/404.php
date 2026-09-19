<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Page Not Found | <?= e($title ?? 'BIMS') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1 d-flex align-items-center justify-content-center p-3">
        <div class="text-center" style="max-width: 480px;">
            <div class="display-1 fw-bold text-primary" aria-hidden="true">404</div>
            <div class="mb-3" aria-hidden="true"><i class="bi bi-compass fs-1 text-muted"></i></div>
            <h1 class="h3 fw-bold mb-2">Page Not Found</h1>
            <p class="text-muted mb-4">The page you are looking for does not exist, may have been moved, or is temporarily unavailable.</p>
            <a href="<?= url('') ?>" class="btn btn-primary btn-lg px-4"><i class="bi bi-house-door me-1"></i>Go Home</a>
        </div>
    </main>
</body>
</html>