<?php
function t($key, $params = []) {
    static $lang = null;
    if ($lang === null) {
        $locale = $_SESSION['lang'] ?? 'en';
        $langFile = ROOT_PATH . '/lang/' . $locale . '.php';
        $lang = file_exists($langFile) ? require $langFile : [];
    }
    $text = $lang[$key] ?? $key;
    foreach ($params as $k => $v) {
        $text = str_replace('{' . $k . '}', $v, $text);
    }
    return $text;
}
