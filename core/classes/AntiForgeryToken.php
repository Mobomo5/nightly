<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 11/4/2015
 * Time: 9:04 PM
 */
class AntiForgeryToken {
    private static $firstRequest = true;
    private $knownToken;
    public static function getInstance() {
        if(! isset($_SESSION['educaskCSRF'])) {
            $_SESSION['educaskCSRF'] = new AntiForgeryToken();
            return $_SESSION['educaskCSRF'];
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $_SESSION['educaskCSRF'];
        }
        if(! self::$firstRequest) {
            return $_SESSION['educaskCSRF'];
        }
        $_SESSION['educaskCSRF']->regenerate();
        self::$firstRequest = false;
        return $_SESSION['educaskCSRF'];
    }
    private function __construct() {
        $this->regenerate();
    }
    public function getHtmlElement() {
        if(! isset($this->knownToken)) {
            return '';
        }
        return "<input type='hidden' value='{$this->knownToken}' id='educaskCSRF' name='educaskCSRF' />";
    }
    public function validate() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        if(! isset($this->knownToken)) {
            return false;
        }
        if(! isset($_POST['educaskCSRF'])) {
            return false;
        }
        if($_POST['educaskCSRF'] !== $this->knownToken) {
            return false;
        }
        return true;
    }
    private function regenerate() {
        $tokenGenerator = new generateRandomString(5, true);
        $this->knownToken = $tokenGenerator->run();
    }
    public function __toString() {
        return '' . $this->getHtmlElement();
    }
}