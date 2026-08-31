<?php
class CSRF {
    public static function token() {
        Session::start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field() {
        return '<input type="hidden" name="_token" value="' . self::token() . '">';
    }

    public static function meta() {
        return '<meta name="csrf-token" content="' . self::token() . '">';
    }

    public static function verify() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return true;

        Session::start();
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('CSRF token mismatch.');
        }
        return true;
    }

    public static function refresh() {
        unset($_SESSION['csrf_token']);
        return self::token();
    }
}
