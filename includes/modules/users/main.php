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
require_once(FORGOT_PASSWORD_OBJECT_FILE);
require_once(FORGOT_PASSWORD_ENGINE_OBJECT_FILE);
require_once(VALIDATOR_OBJECT_FILE);

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
        if($this->params[1] === "login") {
            $this->loginContent();
            return;
        }
        if($this->params[1] === "logout") {
            $this->doLogOut();
            return;
        }
        if($this->params[1] === "forgotPassword") {
            $this->forgotPasswordContent();
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
            return;
        }
        $this->title = 'Login';
        if($this->isPostRequest()) {
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
        $forgotPasswordLink = new link("users/forgotPassword");
        $toReturn .= "<p>Forgot your password? Click <a href=\"{$forgotPasswordLink}\">here</a> to reset it.";
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
        if(! isset($_POST['username']) || ! isset($_POST['password'])) {
            $this->force404 = true;
            return;
        }
        $this->noGUI = true;
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

    public function forgotPasswordContent() {
        if(currentUser::getUserSession()->isLoggedIn()) {
            $this->force404 = true;
            return;
        }
        if($this->isPostRequest()) {
            $this->doForgotPassword();
            return;
        }
        $this->title = 'Request Password Reset';
        $this->content = $this->buildForgotPasswordForm();
    }
    private function buildForgotPasswordForm() {
        $toReturn = "<p>I hear you need a new password. Let's get you a new one.</p>";
        $postLink = new link("users/forgotPassword");
        $toReturn .= "<form action=\"{$postLink}\" method='POST' id=\"forgotPasswordForm\">";
        $antiForgeryToken = new antiForgeryToken();
        $toReturn .= $antiForgeryToken->getHtmlElement();
        $toReturn .= "<label for='username'>Username or email address:</label>";
        $toReturn .= "<input type='text' id='username' name='username' />";
        $toReturn .= "<input type='submit' value='Request Password Reset Token' />";
        $toReturn .= '</form>';
        $loginLink = new link("users/login");
        $toReturn .= "<p>Remember your password? Click <a href=\"{$loginLink}\">here</a> to login.";
        return $toReturn;
    }
    private function doForgotPassword() {
        if(! $this->isPostRequest()) {
            return;
        }
        $antiForgery = new antiForgeryToken();
        if(! $antiForgery->validate()) {
            $this->force404 = true;
            return;
        }
        if(!isset($_POST['username'])) {
            $this->force404 = true;
            return;
        }
        $this->noGUI = true;
        $this->redirectTo = new link('users/forgotPassword');
        $username = preg_replace('/\s+/', '', strip_tags($_POST['username']));
        $validator = new validator('email');
        if($validator->validate($username)) {
            $this->doForgotPasswordByEmail($username);
            return;
        }
    }
    private function doForgotPasswordByEmail($username) {
        $user = userEngine::getInstance()->getUserByEmail($username);
        if($user === false) {
            $this->showSuccessMessageForForgotPassword();
            return;
        }
        $forgotPasswordEngine = forgotPasswordEngine::getInstance();
        $exists = $forgotPasswordEngine->getForgotPasswordByUserID($user->getUserID());
        if($exists !== false) {
            $forgotPasswordEngine->removeForgotPassword($exists);
        }
        $forgotPassword = $forgotPasswordEngine->generateNewForgotPassword($user->getUserID());
        if($forgotPassword === false) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
        $this->showSuccessMessageForForgotPassword();
    }
    private function showSuccessMessageForForgotPassword() {
        noticeEngine::getInstance()->addNotice(new notice(noticeType::success, "Please check your email to continue."));
    }
    private function showErrorMessageForForgotPassword() {
        noticeEngine::getInstance()->addNotice(new notice(noticeType::warning, "Sorry, something went wrong when I tried to generate a password reset token for you. If this keeps happening, please see an administrator."));
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