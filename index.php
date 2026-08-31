<?php
session_start();

define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');

require_once CORE_PATH . '/helpers.php';
require_once CORE_PATH . '/translations.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CSRF.php';
require_once CORE_PATH . '/Validator.php';
require_once CORE_PATH . '/Response.php';

spl_autoload_register(function ($class) {
    $prefixes = [
        'Controllers\\' => CONTROLLERS_PATH,
        'Models\\' => MODELS_PATH,
        'Middleware\\' => ROOT_PATH . '/middleware',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = str_replace($prefix, '', $class);
            $file = $baseDir . '/' . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
    }
});

require_once CORE_PATH . '/App.php';

$app = new App();
$app->run();
