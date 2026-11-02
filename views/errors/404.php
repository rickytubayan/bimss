<?php $isAdmin = $isAdmin ?? false; ?>
<div class="d-flex align-items-center justify-content-center py-5">
    <div class="text-center" style="max-width: 520px;">
        <div class="display-1 fw-bold text-primary" aria-hidden="true">404</div>
        <div class="mb-3" aria-hidden="true"><i class="bi bi-compass fs-1 text-muted"></i></div>
        <h1 class="h3 fw-bold mb-2">Page Not Found</h1>
        <p class="text-muted mb-4">The page you are looking for does not exist, may have been moved, or is temporarily unavailable.</p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="<?= $isAdmin ? admin_url('dashboard') : url('') ?>" class="btn btn-primary px-4">
                <i class="bi bi-house-door me-1"></i><?= $isAdmin ? 'Back to Dashboard' : 'Go Home' ?>
            </a>
            <button type="button" class="btn btn-outline-secondary px-4" onclick="history.back()">
                <i class="bi bi-arrow-left me-1"></i>Go Back
            </button>
        </div>
    </div>
</div>
