<?php
$currentLocale = $_SESSION['lang'] ?? 'en';
$langNames = [
    'en' => 'English',
    'fil' => 'Filipino',
    'bis' => 'Bisaya',
    'ilc' => 'Ilocano',
    'bic' => 'Bicolano',
];
?>
<div class="accessibility-bar py-2" role="region" aria-label="<?= t('accessibility') ?>">
    <div class="container d-flex flex-wrap align-items-center gap-3">
        <span class="acc-title fw-semibold small"><i class="bi bi-universal-access me-1"></i><?= t('accessibility') ?></span>

        <div class="d-flex align-items-center gap-1" role="group" aria-label="<?= t('font_size') ?>">
            <span class="acc-label small text-muted me-1"><?= t('font_size') ?></span>
            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" data-font-scale="decrease" aria-label="<?= t('decrease_font') ?>" data-tts="<?= t('decrease_font') ?>"><span class="small fw-bold">A−</span></button>
            <span class="acc-reading small fw-bold px-1" id="font-scale-indicator" aria-live="polite">100%</span>
            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" data-font-scale="increase" aria-label="<?= t('increase_font') ?>" data-tts="<?= t('increase_font') ?>"><span class="small fw-bold">A+</span></button>
        </div>

        <button type="button" class="btn btn-outline-secondary btn-sm" id="high-contrast-toggle" aria-pressed="false" data-tts="<?= t('contrast') ?>"><i class="bi bi-circle-half me-1"></i><?= t('contrast') ?></button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="dark-mode-toggle" aria-pressed="false" data-tts="<?= t('dark_mode') ?>"><i class="bi bi-moon-stars me-1"></i><?= t('dark_mode') ?></button>
        <button type="button" class="btn btn-primary btn-sm" id="read-aloud-toggle" aria-pressed="false" data-tts="<?= t('read_aloud') ?>"><i class="bi bi-volume-up me-1"></i><?= t('read_aloud') ?></button>

        <div class="d-flex align-items-center gap-1 ms-md-auto">
            <label class="acc-label small text-muted" for="lang-select"><i class="bi bi-translate me-1"></i><?= t('language') ?></label>
            <select id="lang-select" class="form-select form-select-sm acc-select" aria-label="<?= t('language') ?>">
                <?php foreach ($langNames as $code => $name): ?>
                    <option value="<?= $code ?>" <?= $currentLocale === $code ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="acc-reset" data-tts="<?= t('reset') ?>" title="<?= t('reset') ?>"><i class="bi bi-arrow-counterclockwise"></i></button>
        </div>
    </div>
</div>
