<?php
class Auth {
    public static function attempt($email, $password) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') return false;
            self::login($user);
            $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);
            self::logAudit($user['id'], 'login', 'users', $user['id']);
            return true;
        }
        return false;
    }

    public static function login(array $user) {
        Session::start();
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['first_name'] ?? $user['username']);
        Session::set('logged_in', true);
    }

    public static function logout() {
        $userId = self::id();
        if ($userId) {
            self::logAudit($userId, 'logout', 'users', $userId);
        }
        Session::destroy();
    }

    public static function check() {
        Session::start();
        return Session::get('logged_in', false);
    }

    public static function user() {
        if (!self::check()) return null;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([Session::get('user_id')]);
        return $stmt->fetch();
    }

    public static function id() {
        return Session::get('user_id');
    }

    public static function role() {
        return Session::get('user_role');
    }

    public static function hasPermission($module, $action) {
        $role = self::role();
        if (!$role) return false;
        if ($role === 'captain') return true;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as count FROM role_permissions rp
            JOIN roles r ON r.id = rp.role_id
            JOIN permissions p ON p.id = rp.permission_id
            WHERE r.name = ? AND p.module = ? AND p.action = ?
        ");
        $stmt->execute([$role, $module, $action]);
        return (int)$stmt->fetch()['count'] > 0;
    }

    public static function generateOTP() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function sendOTP($email, $otp) {
        $config = require CONFIG_PATH . '/app.php';
        $db = Database::getInstance()->getConnection();
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$config['otp_expiry_minutes']} minutes"));

        $stmt = $db->prepare("INSERT INTO email_logs (recipient_email, subject, body, status, created_at) VALUES (?, ?, ?, 'sent', NOW())");
        $subject = "BIMS - Your Verification Code";
        $body = "Your verification code is: {$otp}. This code expires in {$config['otp_expiry_minutes']} minutes.";
        $stmt->execute([$email, $subject, $body]);

        return true;
    }

    public static function verifyOTP($email, $otp) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM email_logs
            WHERE recipient_email = ? AND body LIKE ? AND status = 'sent'
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$email, "%{$otp}%"]);
        $log = $stmt->fetch();

        if (!$log) return false;

        $stmt = $db->prepare("UPDATE email_logs SET status = 'expired' WHERE id = ?");
        $stmt->execute([$log['id']]);

        $createdAt = new DateTime($log['created_at']);
        $now = new DateTime();
        $config = require CONFIG_PATH . '/app.php';
        $diff = $now->diff($createdAt)->i;

        return $diff <= $config['otp_expiry_minutes'];
    }

    public static function attemptOTP($email, $otp) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && self::verifyOTP($email, $otp)) {
            self::login($user);
            $db->prepare("UPDATE users SET last_login_at = NOW(), email_verified_at = NOW() WHERE id = ?")->execute([$user['id']]);
            self::logAudit($user['id'], 'otp_login', 'users', $user['id']);
            return true;
        }
        return false;
    }

    private static function logAudit($userId, $action, $tableName, $recordId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $tableName, $recordId, get_client_ip(), $_SERVER['HTTP_USER_AGENT'] ?? '']);
    }
}
