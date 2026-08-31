<?php
namespace Middleware;

class AuthMiddleware {
    public function handle() {
        if (!\Auth::check()) {
            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin')) {
                redirect(url('auth/login'));
            } else {
                redirect(url('auth/login'));
            }
            return false;
        }
        return true;
    }
}
