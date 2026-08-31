<?php
namespace Middleware;

class AuditMiddleware {
    public function handle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && \Auth::check()) {
            $_SESSION['audit_referer'] = $_SERVER['HTTP_REFERER'] ?? '';
            $_SESSION['audit_method'] = $_SERVER['REQUEST_METHOD'];
        }
        return true;
    }
}
