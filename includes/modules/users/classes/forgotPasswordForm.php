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
require_once(FORGOT_PASSWORD_OBJECT_FILE);
require_once(FORGOT_PASSWORD_ENGINE_OBJECT_FILE);
require_once(VALIDATOR_OBJECT_FILE);
require_once(MAIL_OBJECT_FILE);
require_once(MAIL_TEMPLATE_OBJECT_FILE);
require_once(MAIL_TEMPLATE_ENGINE_OBJECT_FILE);
require_once(LOCKOUT_ENGINE_OBJECT_FILE);
require_once(LOCKOUT_OBJECT_FILE);
require_once(HONEYPOT_OBJECT_FILE);
class forgotPasswordForm {
    private $title;
    private $content;
    private $force404;
    private $redirectTo;
    private $noGUI;
    private $isPostRequest;
    public function __construct(array $inParams, $isPostRequest = false) {
        if(isset($inParams[3])) {
            $this->force404= true;
            return;
        }
        if(! is_bool($isPostRequest)) {
            $this->isPostRequest = false;
            return;
        }
        $this->isPostRequest = $isPostRequest;
        if($inParams[1] !== "forgotPassword") {
            $this->force404 = true;
            return;
        }
        $lockoutEngine = lockoutEngine::getInstance();
        if($lockoutEngine->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            $this->noGUI = true;
            $this->redirectTo = new link("users/login");
            return;
        }
        if(isset($inParams[2])) {
            $this->secondStep($inParams[2]);
            return;
        }
        $this->forgotPasswordContent();
    }
    public function forgotPasswordContent() {
        if(currentUser::getUserSession()->isLoggedIn()) {
            $this->force404 = true;
            return;
        }
        if($this->isPostRequest) {
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
        $honeypot = new honeypot();
        $toReturn .= $honeypot->getHtmlElement();
        $toReturn .= "<label for='username'>Username or email address:</label>";
        $toReturn .= "<input type='text' id='username' name='username' />";
        $toReturn .= "<input type='submit' value='Request Password Reset Token' />";
        $toReturn .= '</form>';
        $loginLink = new link("users/login");
        $toReturn .= "<p>Remember your password? Click <a href=\"{$loginLink}\">here</a> to login.";
        return $toReturn;
    }
    private function doForgotPassword() {
        if(! $this->isPostRequest) {
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
        $user = userEngine::getInstance()->getUserByUsername($username);
        if($user === false) {
            $this->showSuccessMessageForForgotPassword();
            return;
        }
        $this->addForgotPasswordToDatabase($user);
    }
    private function doForgotPasswordByEmail($username) {
        $user = userEngine::getInstance()->getUserByEmail($username);
        if($user === false) {
            $this->showSuccessMessageForForgotPassword();
            return;
        }
        $this->addForgotPasswordToDatabase($user);
    }
    private function addForgotPasswordToDatabase(user $user) {
        $forgotPasswordEngine = forgotPasswordEngine::getInstance();
        $exists = $forgotPasswordEngine->getForgotPasswordByUserID($user->getUserID());
        if($exists !== false) {
            if($forgotPasswordEngine->forgotPasswordIsOfValidAge($exists)) {
                $this->showSuccessMessageForForgotPassword();
                return;
            }
            $forgotPasswordEngine->removeForgotPassword($exists);
        }
        $forgotPassword = $forgotPasswordEngine->generateNewForgotPassword($user->getUserID());
        if($forgotPassword === false) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
        $mailTemplateEngine = mailTemplateEngine::getInstance();
        $mail = $mailTemplateEngine->loadTemplate("forgotPassword");
        if($mail === false) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
        $mail->addRecipient($user->getEmail());
        $mail->setBulkMail(false);
        $mail->addReplacementValue("[[name]]", $user->getEmail(), $user->getFirstName());
        $passwordTokenLink = new link("users/forgotPassword/{$forgotPassword->getToken()}", false, false, false, true);
        $mail->addReplacementValue("[[passwordToken]]", $user->getEmail(), $passwordTokenLink->getHref());
        if(! $mail->sendMail()) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
        $this->showSuccessMessageForForgotPassword();
    }
    private function secondStep($inParam2) {
        if($this->isPostRequest) {
            $this->secondStepPost($inParam2);
            return;
        }
        $this->title = "Reset Password";
        $token = preg_replace('/\s+/', '', strip_tags($inParam2));
        $forgotPasswordEngine = forgotPasswordEngine::getInstance();
        $forgotPassword = $forgotPasswordEngine->getForgotPasswordByToken($token);
        if($forgotPassword === false) {
            $this->force404 = true;
            return;
        }
        if(!$forgotPasswordEngine->forgotPasswordIsOfValidAge($forgotPassword)) {
            $forgotPasswordEngine->removeForgotPassword($forgotPassword);
            $this->force404 = true;
            return;
        }
        $this->content = $this->buildSecondStepForm($forgotPassword->getToken());
    }
    private function buildSecondStepForm($token) {
        $toReturn = "<p>I hear you need a new password. Let's get you a new one.</p>";
        $postLink = new link("users/forgotPassword/" . $token);
        $toReturn .= "<form action=\"{$postLink}\" method='POST' id=\"forgotPasswordForm\">";
        $antiForgeryToken = new antiForgeryToken();
        $toReturn .= $antiForgeryToken->getHtmlElement();
        $honeypot = new honeypot();
        $toReturn .= $honeypot->getHtmlElement();
        $toReturn .= "<label for='email'>Email address:</label>";
        $toReturn .= "<input type='email' id='email' name='email' />";
        $toReturn .= "<label for='newPassword'>New password:</label>";
        $toReturn .= "<input type='password' id='newPassword' name='newPassword' />";
        $toReturn .= "<label for='confirmNewPassword'>Confirm your new password:</label>";
        $toReturn .= "<input type='password' id='confirmNewPassword' name='confirmNewPassword' />";
        $toReturn .= "<input type='hidden' id='token' name='token' value='{$token}' />";
        $toReturn .= "<input type='submit' value='Proceed' />";
        $toReturn .= '</form>';
        return $toReturn;
    }
    private function secondStepPost($inParam2) {
        if(!$this->isPostRequest) {
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
        if(! isset($_POST['token'])) {
            $this->force404 = true;
            return;
        }
        if(! isset($_POST['email'])) {
            $this->force404 = true;
            return;
        }
        if(! isset($_POST['newPassword'])) {
            $this->force404 = true;
            return;
        }
        if(! isset($_POST['confirmNewPassword'])) {
            $this->force404 = true;
            return;
        }
        $token = preg_replace('/\s+/', '', strip_tags($_POST['token']));
        if($inParam2 !== $token) {
            $this->force404 = true;
            return;
        }
        $forgotPasswordEngine = forgotPasswordEngine::getInstance();
        $forgotPassword1 = $forgotPasswordEngine->getForgotPasswordByToken($token);
        if($forgotPassword1 === false) {
            $this->force404 = true;
            return;
        }
        if(!$forgotPasswordEngine->forgotPasswordIsOfValidAge($forgotPassword1)) {
            $this->force404 = true;
            return;
        }
        $this->noGUI = true;
        $this->redirectTo = new link('users/forgotPassword/' . $token);
        $username = preg_replace('/\s+/', '', strip_tags($_POST['email']));
        $validator = new validator('email');
        if(! $validator->validate($username)) {
            $this->showErrorMessageForForgotPasswordIdentity();
            return;
        }
        $user = userEngine::getInstance()->getUserByEmail($username);
        if($user === false) {
            $this->showErrorMessageForForgotPasswordIdentity();
            return;
        }
        $forgotPassword2 = $forgotPasswordEngine->getForgotPasswordByUserID($user->getUserID());
        if($forgotPassword2 === false) {
            $this->showErrorMessageForForgotPasswordIdentity();
            return;
        }
        if(!$forgotPasswordEngine->forgotPasswordIsOfValidAge($forgotPassword2)) {
            $this->showErrorMessageForForgotPasswordIdentity();
            return;
        }
        if($forgotPassword1->getID() !== $forgotPassword2->getID()) {
            $this->showErrorMessageForForgotPasswordIdentity();
            return;
        }
        if(!$forgotPassword1->verify($forgotPassword2->getToken(), $forgotPassword2->getUserID())) {
            $this->showErrorMessageForForgotPasswordIdentity();
            return;
        }
        if(!$forgotPassword2->verify($forgotPassword1->getToken(), $forgotPassword1->getUserID())) {
            $this->showErrorMessageForForgotPasswordIdentity();
            return;
        }
        $minimumPasswordLength = $forgotPasswordEngine->getMinimumPasswordLength();
        if($_POST['newPassword'] !== $_POST['confirmNewPassword']) {
            $this->showErrorMessageForForgotPasswordNonMatch($minimumPasswordLength);
            return;
        }
        if(! $forgotPasswordEngine->resetUsersPassword($forgotPassword1->getToken(), $forgotPassword2->getUserID(), $_POST['newPassword'], $_POST['confirmNewPassword'])) {
            $this->showErrorMessageForForgotPasswordNonMatch($minimumPasswordLength);
            return;
        }
        $forgotPasswordEngine->removeForgotPassword($forgotPassword1);
        $this->redirectTo = new link('users/login');
        $this->showSuccessMessageForForgotPasswordChange();
    }
    private function showSuccessMessageForForgotPassword() {
        noticeEngine::getInstance()->addNotice(new notice(noticeType::success, "Please check your email to continue."));
    }
    private function showErrorMessageForForgotPassword() {
        noticeEngine::getInstance()->addNotice(new notice(noticeType::warning, "Sorry, something went wrong when I tried to generate a password reset token for you. If this keeps happening, please see an administrator."));
    }
    private function showErrorMessageForForgotPasswordIdentity() {
        noticeEngine::getInstance()->addNotice(new notice(noticeType::warning, "Sorry, I couldn't validate your identity."));
    }
    private function showErrorMessageForForgotPasswordNonMatch($passwordLength) {
        if(! is_numeric($passwordLength)) {
            return;
        }
        noticeEngine::getInstance()->addNotice(new notice(noticeType::warning, "Sorry, your chosen password didn't meet the minimum length requirement (passwords must have {$passwordLength} characters) or didn't match the confirmation password."));
    }
    private function showSuccessMessageForForgotPasswordChange() {
        noticeEngine::getInstance()->addNotice(new notice(noticeType::success, "Your password has been changed."));
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