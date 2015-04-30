<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
require_once(USER_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(ANTI_FORGERY_TOKEN_OBJECT_FILE);
require_once(LINK_OBJECT_FILE);
require_once(LOCKOUT_ENGINE_OBJECT_FILE);
require_once(LOCKOUT_OBJECT_FILE);
require_once(HONEYPOT_OBJECT_FILE);
class loginForm {
    private $title;
    private $content;
    private $force404;
    private $redirectTo;
    private $noGUI;
    private $isPostRequest;
    public function __construct(array $inParams, $isPostRequest = false) {
        if(isset($inParams[2])) {
            $this->force404= true;
            return;
        }
        if(! is_bool($isPostRequest)) {
            $this->isPostRequest = false;
            return;
        }
        $this->isPostRequest = $isPostRequest;
        if($inParams[1] !== "login") {
            $this->force404 = true;
            return;
        }
        $this->loginContent();
    }
    private function loginContent() {
        if(currentUser::getUserSession()->isLoggedIn()) {
            $this->force404 = true;
            return;
        }
        $lockoutEngine = lockoutEngine::getInstance();
        if($lockoutEngine->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            $this->lockoutContent();
            return;
        }
        $this->title = 'Login';
        if($this->isPostRequest) {
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
        $honeyPot = new honeypot();
        $toReturn .= $honeyPot->getHtmlElement();
        $toReturn .= "<label for='username'>Username or email address:</label>";
        $toReturn .= "<input type='text' id='username' name='username' />";
        $toReturn .= "<label for='username'>Password:</label>";
        $toReturn .= "<input type='password' id='password' name='password' />";
        $toReturn .= "<input type='submit' value='Login' />";
        $toReturn .= '</form>';
        $forgotPasswordLink = new link("users/forgotPassword");
        $toReturn .= "<p>Forgot your password? Click <a href=\"{$forgotPasswordLink}\">here</a> to reset it.";
        return $toReturn;
    }
    private function lockoutContent() {
        $this->title .= 'You\'re Locked Out';
        $lockoutEngine = lockoutEngine::getInstance();
        $lockout = $lockoutEngine->getLockout($_SERVER['REMOTE_ADDR']);
        if($lockout === false) {
            return;
        }
        $totalLockoutLength = $lockout->getNumberOfFailedAttempts() * $lockoutEngine->getLockoutPeriod();
        $lockoutStart = clone $lockout->lastUpdated();
        $lockedOutUntil = $lockoutStart->add(DateInterval::createFromDateString($totalLockoutLength . ' minutes'));
        $currentTime = new DateTime();
        $minutesLeft = $currentTime->diff($lockedOutUntil);
        $minutesLeft = ($minutesLeft->days * 24 * 60) + ($minutesLeft->h * 60) + $minutesLeft->i;
        $this->content = "<p>Please wait {$minutesLeft} minutes before trying to log in again.</p>";
    }
    private function doLogIn() {
        if(! $this->isPostRequest) {
            $this->force404 = true;
            return;
        }
        if(! antiForgeryToken::validate()) {
            $this->force404 = true;
            return;
        }
        if(! honeypot::validate()) {
            $this->force404 = true;
            return;
        }
        if(! isset($_POST['username']) || ! isset($_POST['password'])) {
            $this->force404 = true;
            return;
        }
        $this->noGUI = true;
        $lockoutEngine = lockoutEngine::getInstance();
        if($lockoutEngine->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            $this->redirectTo = new link("users/login");
            return;
        }
        $username = preg_replace('/\s+/', '', strip_tags($_POST['username']));
        if (!currentUser::getUserSession()->logIn($username, $_POST['password'])) {
            logger::getInstance()->getInstance()->logIt(new logEntry('1', logEntryType::warning, 'Someone failed to log into ' . $username . '\'s account from IP:' . $_SERVER['REMOTE_ADDR'], 0, new DateTime()), 0);
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
    public function getTitle() {
        return $this->title;
    }
    public function getContent() {
        if($this->force404) {
            return '';
        }
        return $this->content;
    }
    public function forceFourOhFour() {
        return $this->force404;
    }
    public function getReturnPage() {
        return $this->redirectTo;
    }
    public function noGUI() {
        return $this->noGUI;
    }
}