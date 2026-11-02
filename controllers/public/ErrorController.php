<?php
namespace Controllers\Public;

class ErrorController extends \Controller {
    public function notFound() {
        $config = require CONFIG_PATH . '/app.php';
        $barangay = $config['barangay'] ?? [];

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $adminRoles = ['captain', 'kagawad', 'secretary', 'treasurer', 'bhw', 'tanod', 'census', 'sk_chair'];
        $isAdmin = \Auth::check()
            && in_array(\Auth::role(), $adminRoles, true)
            && (bool)preg_match('#/admin(?:/|$)#', $uri);

        $this->view('errors/404', [
            'title' => 'Page Not Found',
            'layout' => $isAdmin ? 'admin/layouts/main' : 'public/layouts/main',
            'isAdmin' => $isAdmin,
            'barangay' => $barangay,
        ]);
    }
}
