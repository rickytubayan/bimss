<?php
namespace Controllers\Public;
class MapController extends \Controller {
    public function index() {
        $this->viewPublic('home/index', ['title' => 'Under Construction']);
    }
    public function __call($name, $args) {
        $this->viewPublic('home/index', ['title' => 'Under Construction']);
    }
}
