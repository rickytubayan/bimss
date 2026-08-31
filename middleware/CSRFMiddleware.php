<?php
namespace Middleware;

class CSRFMiddleware {
    public function handle() {
        \CSRF::verify();
        return true;
    }
}
