<?php
namespace Controllers\Public;

class LanguageController extends \Controller {
    private $supported = ['en', 'fil', 'bis', 'ilc', 'bic'];

    public function set() {
        $lang = $_GET['lang'] ?? 'en';
        if (!in_array($lang, $this->supported)) {
            $lang = 'en';
        }
        $_SESSION['lang'] = $lang;
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'lang' => $lang]);
    }
}
