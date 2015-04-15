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
    private $redirectTo;
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
        if(count($this->params) > 3) {
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
        $this->force404 = true;
    }
    private function loginContent() {
        if(currentUser::getUserSession()->isLoggedIn()) {
            $this->force404 = true;
            return;
        }
        if(isset($this->params[2])) {
            $this->force404 = true;
            return;
        }
        $lockoutEngine = lockoutEngine::getInstance();
        if($lockoutEngine->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            $this->title .= 'You\'re Locked Out';
            $lockout = $lockoutEngine->getLockout($_SERVER['REMOTE_ADDR']);
            if($lockout == false) {
                return;
            }
            $totalLockoutLength = $lockout->getNumberOfFailedAttempts() * $lockoutEngine->getLockoutPeriod();
            $lockoutStart = clone $lockout->lastUpdated();
            $lockedOutUntil = $lockoutStart->add(DateInterval::createFromDateString($totalLockoutLength . ' minutes'));
            $currentTime = new DateTime();
            $minutesLeft = $currentTime->diff($lockedOutUntil);
            $minutesLeft = ($minutesLeft->days * 24 * 60) + ($minutesLeft->h * 60) + $minutesLeft->i;
            $this->content = "<p>Please wait {$minutesLeft} minutes before trying to log in again.</p>";
            return;
        }
        $this->title = 'Login';
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->doLogIn();
            return;
        }
        $this->content = $this->buildLoginForm();
    }

    private function buildLoginForm() {
        $url = "users/login";
        if(isset($_GET['redirectTo'])) {
            $url .= "?redirectTo=" . $_GET['redirectTo'];
        }
        $postLink = new link($url);
        $toReturn = "<form action=\"{$postLink}\" method='POST' id=\"loginForm\">";
        $antiForgeryToken = new antiForgeryToken();
        $toReturn .= $antiForgeryToken->getHtmlElement();
        $toReturn .= "<label for='username'>Username or email address:</label>";
        $toReturn .= "<input type='text' id='username' name='username' />";
        $toReturn .= "<label for='username'>Password:</label>";
        $toReturn .= "<input type='password' id='password' name='password' />";
        $toReturn .= "<input type='submit' value='Login' />";
        $toReturn .= '</form>';
        return $toReturn;
    }

    private function doLogOut() {
        if(isset($this->params[2])) {
            $this->force404 = true;
            return;
        }
        if(! currentUser::getUserSession()->isLoggedIn()) {
            $this->force404 = true;
            return;
        }
        $this->noGUI = true;
        $this->redirectTo = new link('');
        currentUser::getUserSession()->logOut();
    }

    private function doLogIn() {
        $antiForgery = new antiForgeryToken();
        if(! $antiForgery->validate()) {
            $this->force404 = true;
            return;
        }
        $this->noGUI = true;
        if (!currentUser::getUserSession()->logIn($_POST['username'], $_POST['password'])) {
            logger::getInstance()->getInstance()->logIt(new logEntry('1', logEntryType::warning, 'Someone failed to log into ' . $_POST['username'] . '\'s account from IP:' . $_SERVER['REMOTE_ADDR'], 0, new DateTime()), 0);
            noticeEngine::getInstance()->addNotice(new notice(noticeType::warning, 'I couldn\'t log you in.'));
            $this->redirectTo = new link('users/login');
            return;
        }
        if(isset($_GET['redirectTo'])) {
            $this->redirectTo = new link($_GET['redirectTo'],false, false, true);
            return;
        }
        $this->redirectTo = new link('');
    }

    public static function getPageType() {
        return 'user';
    }

    public function noGUI() {
        return $this->noGUI;
    }

    public function getReturnPage() {
        return $this->redirectTo;
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