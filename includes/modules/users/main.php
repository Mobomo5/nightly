<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/21/14
 * Time: 2:02 PM
 */
require_once(MODULE_INTERFACE_FILE);
require_once(ANTI_FORGERY_TOKEN_OBJECT_FILE);
require_once(LINK_OBJECT_FILE);

class users implements module {
    private $params;
    private $noGUI = false;
    private $module;
    private $force404 = false;
    private $title;
    private $content;

    public function __construct() {
        $this->params = router::getInstance()->getParameters(true);
        $this->module = $this->params[0];

        if(! isset($this->params[1])) {
            $this->force404 = true;
            return;
        }
        if (empty($this->params[1])) {
            $this->force404 = true;
            return;
        }
        if($this->params[1] == "login") {
            $this->loginContent();
            return;
        }
        if($this->params[1] == "logout") {
            $this->doLogOut();
            return;
        }
    }
    private function loginContent() {
        if(currentUser::getUserSession()->isLoggedIn()) {
            $this->force404 = true;
            return;
        }
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->doLogIn();
            return;
        }
        $this->title = 'Login';
        $postLink = new link("users/login");
        $this->content = "<form action='" . $postLink . "' method='POST'>";
        $antiForgeryToken = new antiForgeryToken();
        $this->content .= $antiForgeryToken->getHtmlElement();
        $this->content .= "<label for='username'>Username or email address:</label>";
        $this->content .= "<input type='text' id='username' name='username' />";
        $this->content .= "<label for='username'>Password:</label>";
        $this->content .= "<input type='password' id='password' name='password' />";
        $this->content .= "<input type='submit' value='Login' />";
        $this->content .= '</form>';
    }

    private function doLogOut() {
        if(! currentUser::getUserSession()->isLoggedIn()) {
            $this->force404 = true;
            return;
        }
        return currentUser::getUserSession()->logOut();
    }

    private function doLogIn() {
        $antiForgery = new antiForgeryToken();
        if(! $antiForgery->validate()) {
            $this->force404 = true;
            return;
        }
        if (!currentUser::getUserSession()->logIn($_POST['username'], $_POST['password'])) {
            logger::getInstance()->getInstance()->logIt(new logEntry('1', logEntryType::warning, 'Someone failed to log into ' . $_POST['username'] . '\'s account from IP:' . $_SERVER['REMOTE_ADDR'], 0, new DateTime()), 0);
            noticeEngine::getInstance()->addNotice(new notice(noticeType::error, 'I couldn\'t log you in.'));
        }
    }

    public static function getPageType() {
        return 'user';
    }

    public function noGUI() {
        return $this->noGUI;
    }

    public function getReturnPage() {
        return new link('');
    }

    public function getPageContent() {
        if($this->force404) {
            return '';
        }
        return $this->content;
    }

    public function getTitle() {
        return $this->title;
    }

    public function forceFourOhFour() {
        return $this->force404;
    }
}