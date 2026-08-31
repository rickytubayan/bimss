<?php $isAdmin = false; ?>
<div class="page-header">
    <h1 class="page-title"><?= t('welcome') ?>, <span class="text-primary"><?= e($user ?? '') ?></span>!</h1>
    <p class="page-subtitle"><?= t('quick_links') ?></p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="#" class="card text-center text-decoration-none shadow-sm h-100 p-3">
            <div class="empty-state-icon text-primary" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></div>
            <strong><?= t('my_documents') ?></strong>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="#" class="card text-center text-decoration-none shadow-sm h-100 p-3">
            <div class="empty-state-icon text-primary" aria-hidden="true"><i class="bi bi-calendar-check"></i></div>
            <strong><?= t('my_appointments') ?></strong>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="#" class="card text-center text-decoration-none shadow-sm h-100 p-3">
            <div class="empty-state-icon text-primary" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></div>
            <strong><?= t('my_complaints') ?></strong>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="#" class="card text-center text-decoration-none shadow-sm h-100 p-3">
            <div class="empty-state-icon text-primary" aria-hidden="true"><i class="bi bi-person"></i></div>
            <strong><?= t('my_profile') ?></strong>
        </a>
    </div>
</div>

<?php if (!empty($notifications)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex align-items-center"><i class="bi bi-bell me-2 text-primary"></i><?= t('latest_updates') ?></div>
    <ul class="list-group list-group-flush">
        <?php foreach ($notifications as $n): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start gap-3">
                <div>
                    <strong><?= e($n['title']) ?></strong>
                    <div class="text-muted small"><?= e($n['message']) ?></div>
                </div>
                <span class="badge text-bg-light text-nowrap small"><?= time_ago($n['created_at']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if (!empty($recentBulletins)): ?>
<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center"><i class="bi bi-megaphone me-2 text-primary"></i><?= t('announcements') ?></div>
    <ul class="list-group list-group-flush">
        <?php foreach ($recentBulletins as $b): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center gap-3">
                <div>
                    <strong><?= e($b['title']) ?></strong>
                    <div class="text-muted small"><?= truncate(strip_tags($b['content']), 150) ?></div>
                </div>
                <span class="badge rounded-pill text-bg-primary text-nowrap"><?= format_date($b['published_at']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon" aria-hidden="true"><i class="bi bi-megaphone"></i></div>
        <p><?= t('no_results') ?></p>
    </div>
<?php endif; ?>
