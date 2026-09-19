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

    private static function logAudit($userId, $action, $tableName, $recordId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $tableName, $recordId, get_client_ip(), $_SERVER['HTTP_USER_AGENT'] ?? '']);
    }
}
