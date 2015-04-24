<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/21/14
 * Time: 2:02 PM
 */
require_once(MODULE_INTERFACE_FILE);
class users implements module {
    private $subModule;
    private $force404;
    public function __construct() {
        $params = router::getInstance()->getParameters(true);
        if(! isset($params[1])) {
            $this->force404 = true;
            return;
        }
        if (empty($params[1])) {
            $this->force404 = true;
            return;
        }
        $this->force404 = false;
        if($params[1] === "login") {
            require_once('classes/loginForm.php');
            $this->subModule = new loginForm($params, $this->isPostRequest());
            return;
        }
        if($params[1] === "logout") {
            require_once('classes/logoutForm.php');
            $this->subModule = new logoutForm($params, $this->isPostRequest());
            return;
        }
        if($params[1] === "forgotPassword") {
            require_once('classes/forgotPasswordForm.php');
            $this->subModule = new forgotPasswordForm($params, $this->isPostRequest());
            return;
        }
        $this->force404 = true;
    }
    private function isPostRequest() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        return true;
    }
    public static function getPageType() {
        return 'user';
    }
    public function noGUI() {
        if($this->force404) {
            return false;
        }
        return $this->subModule->noGUI();
    }
    public function getReturnPage() {
        if($this->force404) {
            return '';
        }
        return $this->subModule->getReturnPage();
    }
    public function getPageContent() {
        if($this->force404) {
            return '';
        }
        return $this->subModule->getContent();
    }
    public function getTitle() {
        if($this->force404) {
            return '';
        }
        return $this->subModule->getTitle();
    }
    public function forceFourOhFour() {
        if($this->force404) {
            return true;
        }
        return $this->subModule->forceFourOhFour();
    }
}