<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 11/4/2015
 * Time: 9:04 PM
 */
require_once(GENERAL_ENGINE_OBJECT_FILE);
require_once(GENERAL_FUNCTION_INTERFACE_FILE);
class antiForgeryToken {
    private $knownToken = null;
    public function __construct() {
        $general = new general("generateRandomString");
        if(! $general->functionsExists()) {
            return;
        }
        $this->knownToken = $general->run(array('randomLength' => true));
    }
    public function getHtmlElement() {
        if($this->knownToken == null) {
            return '';
        }
        $_SESSION['educaskCSRF'] = $this->knownToken;
        return '<input type="hidden" value="' . $this->knownToken . '" id="educaskCSRF" name="educaskCSRF" />';
    }
    public function validate() {
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            return false;
        }
        if(! isset($_SESSION['educaskCSRF'])) {
            return false;
        }
        $knownToken = $_SESSION['educaskCSRF'];
        if($knownToken == null) {
            return false;
        }
        $_SESSION['educaskCSRF'] = null;
        if(! isset($_POST['educaskCSRF'])) {
            return false;
        }
        $givenToken = $_POST['educaskCSRF'];
        if($givenToken != $knownToken) {
            return false;
        }
        return true;
    }
    public function __toString() {
        return '' . $this->getHtmlElement();
    }
}