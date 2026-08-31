<?php
namespace Controllers\Public;
class DocumentRequestController extends \Controller {
    public function index() {
        $this->viewPublic('public/home/index', ['title' => 'Under Construction']);
    }
    public function __call($name, $args) {
        $this->viewPublic('public/home/index', ['title' => 'Under Construction']);
    }
}
