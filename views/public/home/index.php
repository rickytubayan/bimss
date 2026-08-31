<?php $isAdmin = false; ?>
<section class="hero">
    <h1 class="display-5 fw-bold"><?= t('welcome') ?>! <i class="bi bi-hand-thumbs-up text-primary"></i></h1>
    <p class="lead" style="margin:0 auto"><?= t('welcome_message') ?></p>
</section>

<div class="service-grid" role="list" aria-label="<?= t('services') ?>">
    <a href="<?= url('public/documents') ?>" class="service-tile" role="listitem" data-read-aloud="<?= t('request_document') ?>">
        <div class="service-icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></div>
        <span class="service-label"><?= t('request_document') ?></span>
        <span class="service-desc"><?= t('request_clearance') ?></span>
    </a>

    <a href="<?= url('public/documents/track/TEST') ?>" class="service-tile" role="listitem" data-read-aloud="<?= t('track_request') ?>">
        <div class="service-icon" aria-hidden="true"><i class="bi bi-search"></i></div>
        <span class="service-label"><?= t('track_request') ?></span>
    </a>

    <a href="<?= url('public/appointments') ?>" class="service-tile" role="listitem" data-read-aloud="<?= t('book_appointment') ?>">
        <div class="service-icon" aria-hidden="true"><i class="bi bi-calendar-check"></i></div>
        <span class="service-label"><?= t('book_appointment') ?></span>
    </a>

    <a href="<?= url('public/complaints') ?>" class="service-tile" role="listitem" data-read-aloud="<?= t('report_problem') ?>">
        <div class="service-icon" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></div>
        <span class="service-label"><?= t('report_problem') ?></span>
    </a>

    <a href="<?= url('public/blotter') ?>" class="service-tile" role="listitem" data-read-aloud="<?= t('file_blotter') ?>">
        <div class="service-icon" aria-hidden="true"><i class="bi bi-journal-text"></i></div>
        <span class="service-label"><?= t('file_blotter') ?></span>
    </a>

    <a href="<?= url('public/bulletin') ?>" class="service-tile" role="listitem" data-read-aloud="<?= t('view_bulletin') ?>">
        <div class="service-icon" aria-hidden="true"><i class="bi bi-megaphone"></i></div>
        <span class="service-label"><?= t('view_bulletin') ?></span>
    </a>

    <a href="<?= url('public/map') ?>" class="service-tile" role="listitem" data-read-aloud="<?= t('map') ?>">
        <div class="service-icon" aria-hidden="true"><i class="bi bi-geo-alt"></i></div>
        <span class="service-label"><?= t('map') ?></span>
    </a>

    <a href="<?= url('public/transparency') ?>" class="service-tile" role="listitem" data-read-aloud="<?= t('transparency') ?>">
        <div class="service-icon" aria-hidden="true"><i class="bi bi-bar-chart-line"></i></div>
        <span class="service-label"><?= t('transparency') ?></span>
    </a>
</div>

<div class="emergency-button-fixed">
    <a href="<?= url('public/emergency') ?>" class="emergency-button" id="emergency-panic"
       data-read-aloud="<?= t('emergency') ?>" aria-label="<?= t('emergency') ?>" title="<?= t('emergency') ?>">SOS</a>
</div>
