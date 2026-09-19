<?php
$typeColors = ['in_app' => 'badge-blue', 'email' => 'badge-gray', 'emergency' => 'badge-red'];
$typeLabels = ['in_app' => 'In-app', 'email' => 'Email', 'emergency' => 'Emergency'];
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle mb-0"><?= number_format($stats['unread']) ?> unread · <?= number_format($total) ?> shown</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('notifications/emergency/send') ?>" class="btn btn-danger"><i class="bi bi-broadcast me-1"></i>Send Emergency Alert</a>
        <a href="#broadcast" class="btn btn-primary"><i class="bi bi-send-plus me-1"></i>Broadcast</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-bell"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Notifications"><?= $stats['total'] ?></div>
            <div class="stat-label">Total</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-bell-fill"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Unread"><?= $stats['unread'] ?></div>
            <div class="stat-label">Unread</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check2-all"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Read"><?= $stats['read'] ?></div>
            <div class="stat-label">Read</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Emergency Alerts"><?= $stats['emergency'] ?></div>
            <div class="stat-label">Emergency</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span><i class="bi bi-bell me-2 text-primary"></i>Inbox</span>
                <ul class="nav nav-pills nav-sm">
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $read === '' ? 'active' : '' ?>" href="<?= admin_url('notifications?type=' . urlencode($type) . '&q=' . urlencode($search)) ?>">All</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $read === 'unread' ? 'active' : '' ?>" href="<?= admin_url('notifications?read=unread&type=' . urlencode($type) . '&q=' . urlencode($search)) ?>">Unread</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-2 <?= $read === 'read' ? 'active' : '' ?>" href="<?= admin_url('notifications?read=read&type=' . urlencode($type) . '&q=' . urlencode($search)) ?>">Read</a></li>
                </ul>
            </div>
            <form method="GET" action="<?= admin_url('notifications') ?>" class="row g-2 p-3 pb-0">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search title or message..." value="<?= e($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All types</option>
                        <?php foreach ($typeLabels as $t => $lbl): ?>
                            <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <?php $unread = (int)$n['is_read'] === 0; ?>
                    <div class="list-group-item <?= $unread ? 'bg-primary-subtle' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <?php if ($unread): ?><i class="bi bi-circle-fill text-danger small" title="Unread"></i><?php endif; ?>
                                    <strong class="<?= $unread ? '' : 'text-muted' ?>"><?= e($n['title']) ?></strong>
                                    <span class="badge rounded-pill <?= $typeColors[$n['type']] ?? 'badge-gray' ?>"><?= $typeLabels[$n['type']] ?? ucfirst($n['type']) ?></span>
                                </div>
                                <div class="text-muted small"><?= e($n['message']) ?></div>
                                <?php if (!empty($n['link'])): ?>
                                    <a class="small" href="<?= e($n['link']) ?>" target="_blank">Related page <i class="bi bi-box-arrow-up-right"></i></a>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                <span class="badge text-bg-light text-nowrap small"><?= time_ago($n['created_at']) ?></span>
                                <?php if ($unread): ?>
                                    <form method="POST" action="<?= admin_url('notifications/mark-read/' . $n['id']) ?>" class="d-inline">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as read"><i class="bi bi-check2"></i> Mark read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($notifications)): ?>
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-bell-slash d-block fs-3 mb-2"></i>No notifications found.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="mt-3"><ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= admin_url('notifications?page=' . $i . '&type=' . urlencode($type) . '&read=' . urlencode($read) . '&q=' . urlencode($search)) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul></nav>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3" id="broadcast">
            <div class="card-header d-flex align-items-center"><i class="bi bi-send-plus me-2 text-primary"></i>Broadcast Notification</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('notifications/broadcast') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= e(old('title')) ?>" placeholder="e.g. Advisory: Water interruption" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="message">Message <span class="text-danger">*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="4" placeholder="Details of the announcement..." required><?= e(old('message')) ?></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="in_app" <?= old('type') === 'in_app' ? 'selected' : '' ?>>In-app</option>
                                <option value="email" <?= old('type') === 'email' ? 'selected' : '' ?>>Email</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label" for="audience">Audience <span class="text-danger">*</span></label>
                            <select id="audience" name="audience" class="form-select" required>
                                <option value="all" <?= old('audience') === 'all' ? 'selected' : '' ?>>All users</option>
                                <option value="admins" <?= old('audience') === 'admins' ? 'selected' : '' ?>>Barangay officials</option>
                                <option value="residents" <?= old('audience') === 'residents' ? 'selected' : '' ?>>Resident accounts</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="link">Link (optional)</label>
                        <input type="text" id="link" name="link" class="form-control" value="<?= e(old('link')) ?>" placeholder="e.g. /admin/bulletins">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-send me-1"></i>Broadcast</button>
                </form>
            </div>
        </div>

        <?php if (!empty($templates)): ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center"><i class="bi bi-file-text me-2 text-primary"></i>Notification Templates</div>
            <div class="card-body">
                <div class="mb-2">
                    <select id="template-select" class="form-select">
                        <option value="">Choose a template…</option>
                        <?php foreach ($templates as $tpl): ?>
                            <option value="<?= e($tpl['template_text']) ?>"><?= e($tpl['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Picks a message template. Edit the placeholders before sending.</div>
                </div>
                <?php foreach ($templates as $tpl): ?>
                    <div class="border rounded p-2 mb-2 small">
                        <strong><?= e($tpl['name']) ?></strong>
                        <div class="text-muted"><?= e($tpl['template_text']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var sel = document.getElementById('template-select');
                var msg = document.getElementById('message');
                if (sel && msg) {
                    sel.addEventListener('change', function () {
                        if (this.value) msg.value = this.value;
                    });
                }
            });
        </script>
        <?php endif; ?>
    </div>
</div>