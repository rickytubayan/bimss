<?php
namespace Controllers\Admin;
class FinanceController extends \Controller {
    public function __call($name, $args) {
        $this->viewAdmin('dashboard/index', ['title' => 'Under Construction']);
    }
}
