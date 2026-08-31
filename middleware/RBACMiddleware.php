<?php
namespace Middleware;

class RBACMiddleware {
    public function handle() {
        if (!\Auth::check()) return false;

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        if (str_starts_with($uri, 'admin')) {
            $role = \Auth::role();
            $adminRoles = ['captain', 'kagawad', 'secretary', 'treasurer', 'bhw', 'tanod', 'census', 'sk_chair'];
            if (!in_array($role, $adminRoles)) {
                redirect(url('public/dashboard'));
                return false;
            }

            $module = $this->extractModule($uri);
            if ($module && !\Auth::hasPermission($module, 'view') && $role !== 'captain') {
                http_response_code(403);
                die('Access denied. You do not have permission to access this module.');
            }
        }
        return true;
    }

    private function extractModule($uri) {
        $parts = explode('/', trim($uri, '/'));
        return $parts[1] ?? null;
    }
}
