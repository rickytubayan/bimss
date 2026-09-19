<?php
namespace Controllers\Admin;

class NotificationController extends \Controller {

    private const TYPES = ['in_app', 'email', 'emergency'];
    private const SEVERITIES = ['low', 'medium', 'high', 'critical'];
    private const ADMIN_ROLES = ['captain', 'kagawad', 'secretary', 'treasurer', 'bhw', 'tanod', 'census', 'sk_chair'];

    public function index() {
        $db = $this->db;
        $userId = \Auth::id();

        $search = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? '';
        $read = $_GET['read'] ?? '';

        $where = "WHERE n.deleted_at IS NULL AND n.recipient_id = ?";
        $params = [$userId];

        if ($search !== '') {
            $where .= " AND (n.title LIKE ? OR n.message LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
        }
        if (in_array($type, self::TYPES, true)) {
            $where .= " AND n.type = ?";
            $params[] = $type;
        }
        if ($read === 'unread') {
            $where .= " AND n.is_read = 0";
        } elseif ($read === 'read') {
            $where .= " AND n.is_read = 1";
        }

        $perPage = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM notifications n {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $db->prepare("
            SELECT n.* FROM notifications n
            {$where}
            ORDER BY n.is_read ASC, n.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $notifications = $stmt->fetchAll();

        $stats = [
            'total' => (int)$db->query("SELECT COUNT(*) c FROM notifications WHERE recipient_id = {$userId} AND deleted_at IS NULL")->fetch()['c'],
            'unread' => (int)$db->query("SELECT COUNT(*) c FROM notifications WHERE recipient_id = {$userId} AND deleted_at IS NULL AND is_read = 0")->fetch()['c'],
            'read' => (int)$db->query("SELECT COUNT(*) c FROM notifications WHERE recipient_id = {$userId} AND deleted_at IS NULL AND is_read = 1")->fetch()['c'],
            'emergency' => (int)$db->query("SELECT COUNT(*) c FROM notifications WHERE recipient_id = {$userId} AND deleted_at IS NULL AND type = 'emergency'")->fetch()['c'],
        ];

        $templates = $db->query("SELECT * FROM notification_templates WHERE deleted_at IS NULL AND is_active = 1 ORDER BY name")->fetchAll();

        $this->viewAdmin('notifications/index', [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'stats' => $stats,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $perPage),
            'search' => $search,
            'type' => $type,
            'read' => $read,
            'templates' => $templates,
        ]);
    }

    public function markRead($id) {
        $db = $this->db;
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE id = ? AND recipient_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id, \Auth::id()]);
        flash('success', 'Notification marked as read.');
        redirect(admin_url('notifications'));
    }

    public function broadcast() {
        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty(trim($input['title'] ?? ''))) $errors[] = 'Notification title is required.';
        if (empty(trim($input['message'] ?? ''))) $errors[] = 'Notification message is required.';
        if (!in_array($input['type'] ?? '', ['in_app', 'email'], true)) $errors[] = 'Please select a valid notification type.';
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('notifications'));
        }

        $recipients = $this->buildRecipients($input['audience'] ?? 'all');

        if (empty($recipients)) {
            set_old_input($input);
            flash('error', 'No active user accounts match the selected audience.');
            redirect(admin_url('notifications'));
        }

        $title = trim($input['title']);
        $message = trim($input['message']);
        $type = $input['type'];
        $link = trim($input['link'] ?? '') === '' ? null : trim($input['link']);

        $stmt = $db->prepare("INSERT INTO notifications (recipient_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        $db->beginTransaction();
        try {
            foreach ($recipients as $rid) {
                $stmt->execute([$rid, $title, $message, $type, $link]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            flash('error', 'Could not send the notification. Please try again.');
            redirect(admin_url('notifications'));
        }

        flash('success', 'Notification broadcast to ' . count($recipients) . ' user account(s).');
        redirect(admin_url('notifications'));
    }

    public function emergencyForm() {
        $db = $this->db;

        $puroks = $db->query("SELECT id, name, type FROM puroks WHERE deleted_at IS NULL ORDER BY name")->fetchAll();

        $alerts = $db->query("
            SELECT a.*, u.username AS sent_by_name
            FROM emergency_alerts a
            LEFT JOIN users u ON u.id = a.sent_by
            WHERE a.deleted_at IS NULL
            ORDER BY a.created_at DESC
            LIMIT 20
        ")->fetchAll();

        $this->viewAdmin('notifications/emergency', [
            'title' => 'Emergency Alert',
            'puroks' => $puroks,
            'alerts' => $alerts,
            'canSend' => \Auth::hasPermission('notifications', 'send_emergency'),
        ]);
    }

    public function sendEmergency() {
        if (!\Auth::hasPermission('notifications', 'send_emergency')) {
            flash('error', 'You do not have permission to send emergency alerts.');
            redirect(admin_url('notifications/emergency/send'));
        }

        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty(trim($input['alert_type'] ?? ''))) $errors[] = 'Alert type is required.';
        if (empty(trim($input['message'] ?? ''))) $errors[] = 'Alert message is required.';
        if (!in_array($input['severity'] ?? '', self::SEVERITIES, true)) $errors[] = 'Please select a valid severity.';
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('notifications/emergency/send'));
        }

        $purokIds = array_values(array_filter(array_map('intval', (array)($input['target_puroks'] ?? []))));
        $purokJson = empty($purokIds) ? null : json_encode($purokIds);

        $recipients = $this->buildRecipients('all');
        $alertTitle = 'Emergency Alert: ' . trim($input['alert_type']);
        $alertMessage = strtoupper($input['severity']) . ' - ' . trim($input['message']);

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO emergency_alerts (alert_type, message, target_puroks, severity, sent_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([trim($input['alert_type']), trim($input['message']), $purokJson, $input['severity'], \Auth::id()]);

            $notify = $db->prepare("INSERT INTO notifications (recipient_id, title, message, type, link) VALUES (?, ?, ?, 'emergency', ?)");
            foreach ($recipients as $rid) {
                $notify->execute([$rid, $alertTitle, $alertMessage, null]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            flash('error', 'Could not send the emergency alert. Please try again.');
            redirect(admin_url('notifications/emergency/send'));
        }

        flash('success', 'Emergency alert sent to ' . count($recipients) . ' user account(s).');
        redirect(admin_url('notifications/emergency/send'));
    }

    private function buildRecipients($audience) {
        $db = $this->db;
        $roleClause = '';
        if ($audience === 'admins') {
            $roleClause = " AND role IN ('" . implode("','", self::ADMIN_ROLES) . "')";
        } elseif ($audience === 'residents') {
            $roleClause = " AND role = 'resident'";
        }
        $rows = $db->query("SELECT id FROM users WHERE deleted_at IS NULL AND status = 'active'{$roleClause}")->fetchAll(\PDO::FETCH_COLUMN);
        return array_map('intval', $rows);
    }
}