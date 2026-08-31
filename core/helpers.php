<?php
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function url($path = '') {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $base . '/' . ltrim($path, '/');
}

function public_url($path = '') {
    return url('public/' . ltrim($path, '/'));
}

function admin_url($path = '') {
    return url('admin/' . ltrim($path, '/'));
}

function asset($path) {
    return url('public/' . ltrim($path, '/'));
}

function old($key, $default = '') {
    return $_POST[$key] ?? $_SESSION['old_input'][$key] ?? $default;
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

function set_old_input(array $data) {
    $_SESSION['old_input'] = $data;
}

function clear_old_input() {
    unset($_SESSION['old_input']);
}

function format_currency($amount) {
    return '₱' . number_format((float)$amount, 2, '.', ',');
}

function format_date($date, $format = 'M d, Y') {
    if (!$date) return '';
    $timestamp = is_string($date) ? strtotime($date) : $date;
    return date($format, $timestamp);
}

function generate_tracking_code($prefix = 'BIMS') {
    return $prefix . '-' . strtoupper(substr(uniqid(), -6)) . '-' . rand(100, 999);
}

function generate_qr_data($data) {
    return json_encode($data);
}

function time_ago($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function sanitize_filename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function is_get() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function get_client_ip() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function truncate($string, $length = 100, $suffix = '...') {
    if (mb_strlen($string) <= $length) return $string;
    return mb_substr($string, 0, $length) . $suffix;
}
