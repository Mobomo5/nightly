<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class forgotPasswordForm implements IModule {
    private $request;
    private $response;
    public function __construct(Request $request) {
        $inParams = $request->getParameters(true);
        if(isset($inParams[3])) {
            $this->response = Response::fourOhFour();
            return;
        }
        if($inParams[1] !== "forgotPassword") {
            $this->response = Response::fourOhFour();
            return;
        }
        if(CurrentUser::getUserSession()->isLoggedIn()) {
            $this->response = Response::fourOhFour();
            return;
        }
        $this->request = $request;
        $lockoutEngine = LockoutEngine::getInstance();
        if($lockoutEngine->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            $this->response = Response::redirect(new Link("users/login"));
            return;
        }
        if(isset($inParams[2])) {
            $this->secondStep($inParams[2]);
            return;
        }
        $this->forgotPasswordContent();
    }
    public function forgotPasswordContent() {
        if($this->request->isPostRequest()) {
            $this->doForgotPassword();
            return;
        }
        $this->response = new Response(200, "@users/forgotPassword.twig", "Forgot Password", "users");
    }
    private function doForgotPassword() {
        if(! $this->request->isPostRequest()) {
            return;
        }
        if(! AntiForgeryToken::getInstance()->validate()) {
            $this->response = Response::fiveHundred();
            return;
        }
        if(! Honeypot::getInstance()->validate()) {
            $this->response = Response::fiveHundred();
            return;
        }
        $username = Request::getPostParameter('username');
        if(!$username) {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, "All fields are required."));
            $this->response = Response::redirect(new Link("users/forgotPassword"));
            return;
        }
        $this->response = Response::redirect(new Link("users/forgotPassword/confirmation"));
        $username = preg_replace('/\s+/', '', strip_tags($_POST['username']));
        $validator = new emailValidator();
        if($validator->validate($username)) {
            $this->doForgotPasswordByEmail($username);
            return;
        }
        $user = UserEngine::getInstance()->getUserByUsername($username);
        if($user === false) {
            return;
        }
        if($user->isExternalAuthentication()) {
            $this->sendExternalAuthenticationEmail($user);
            return;
        }
        $this->addForgotPasswordToDatabase($user);
    }
    private function doForgotPasswordByEmail($username) {
        $user = UserEngine::getInstance()->getUserByEmail($username);
        if($user === false) {
            return;
        }
        $this->addForgotPasswordToDatabase($user);
    }
    private function addForgotPasswordToDatabase(User $user) {
        $forgotPasswordEngine = ForgotPasswordEngine::getInstance();
        $exists = $forgotPasswordEngine->getForgotPasswordByUserID($user->getUserID());
        if(is_object($exists)) {
            if($forgotPasswordEngine->forgotPasswordIsOfValidAge($exists)) {
                return;
            }
            $forgotPasswordEngine->removeForgotPassword($exists);
        }
        $forgotPassword = $forgotPasswordEngine->generateNewForgotPassword($user->getUserID());
        if($forgotPassword === false) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
        $mailTemplateEngine = MailTemplateEngine::getInstance();
        $mail = $mailTemplateEngine->loadTemplate("forgotPassword");
        if($mail === false) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
        $mail->addRecipient($user->getEmail());
        $mail->setBulkMail(false);
        $mail->addReplacementValue("[[name]]", $user->getEmail(), $user->getFirstName());
        $passwordTokenLink = new Link("users/forgotPassword/{$forgotPassword->getToken()}", false, false, false, true);
        $mail->addReplacementValue("[[passwordToken]]", $user->getEmail(), $passwordTokenLink->getHref());
        if(! $mail->sendMail()) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
    }
    private function secondStep($inParam2) {
        if($inParam2 === "confirmation") {
            $this->response = new Response(200, "@users/forgotPasswordConfirmation.twig", "Recover your Password", "users");
            return;
        }
        if($this->request->isPostRequest()) {
            $this->secondStepPost($inParam2);
            return;
        }
        $token = preg_replace('/\s+/', '', strip_tags($inParam2));
        $forgotPasswordEngine = ForgotPasswordEngine::getInstance();
        $forgotPassword = $forgotPasswordEngine->getForgotPasswordByToken($token);
        if($forgotPassword === false) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(!$forgotPasswordEngine->forgotPasswordIsOfValidAge($forgotPassword)) {
            $forgotPasswordEngine->removeForgotPassword($forgotPassword);
            $this->response = Response::fourOhFour();
            return;
        }
        $this->response = new Response(200, "@users/forgotPasswordResetPassword.twig", "Reset Password", "users", $forgotPassword);
    }
    private function secondStepPost($inParam2) {
        if(!$this->request->isPostRequest()) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! AntiForgeryToken::getInstance()->validate()) {
            $this->response = Response::fiveHundred();
            return;
        }
        if(! Honeypot::getInstance()->validate()) {
            $this->response = Response::fiveHundred();
            return;
        }
        $token = Request::getPostParameter('token');
        $email = Request::getPostParameter('email');
        $newPassword = Request::getPostParameter('newPassword');
        $confirmNewPassword = Request::getPostParameter('confirmNewPassword');
        if($token === false) {
            $this->response = Response::fiveHundred();
            return;
        }
        if($email === false) {
            $this->response = Response::fiveHundred();
            return;
        }
        if($newPassword === false) {
            $this->response = Response::fiveHundred();
            return;
        }
        if($confirmNewPassword === false) {
            $this->response = Response::fiveHundred();
            return;
        }
        $token = preg_replace('/\s+/', '', strip_tags($token));
        if($inParam2 !== $token) {
            $this->response = Response::fiveHundred();
            return;
        }
        $forgotPasswordEngine = ForgotPasswordEngine::getInstance();
        $forgotPassword1 = $forgotPasswordEngine->getForgotPasswordByToken($token);
        if($forgotPassword1 === false) {
            $this->response = Response::fiveHundred();
            return;
        }
        if(!$forgotPasswordEngine->forgotPasswordIsOfValidAge($forgotPassword1)) {
            $this->response = Response::fourOhFour();
            return;
        }
        $username = preg_replace('/\s+/', '', strip_tags($email));
        $validator = new emailValidator();
        if(! $validator->validate($username)) {
            $this->showErrorMessageForForgotPasswordIdentity();
            $this->redirectOnError($inParam2);
            return;
        }
        $user = UserEngine::getInstance()->getUserByEmail($username);
        if($user === false) {
            $this->showErrorMessageForForgotPasswordIdentity();
            $this->redirectOnError($inParam2);
            return;
        }
        $forgotPassword2 = $forgotPasswordEngine->getForgotPasswordByUserID($user->getUserID());
        if($forgotPassword2 === false) {
            $this->showErrorMessageForForgotPasswordIdentity();
            $this->redirectOnError($inParam2);
            return;
        }
        if(!$forgotPasswordEngine->forgotPasswordIsOfValidAge($forgotPassword2)) {
            $this->showErrorMessageForForgotPasswordIdentity();
            $this->redirectOnError($inParam2);
            return;
        }
        if($forgotPassword1->getID() !== $forgotPassword2->getID()) {
            $this->showErrorMessageForForgotPasswordIdentity();
            $this->redirectOnError($inParam2);
            return;
        }
        if(!$forgotPassword1->verify($forgotPassword2->getToken(), $forgotPassword2->getUserID())) {
            $this->showErrorMessageForForgotPasswordIdentity();
            $this->redirectOnError($inParam2);
            return;
        }
        if(!$forgotPassword2->verify($forgotPassword1->getToken(), $forgotPassword1->getUserID())) {
            $this->showErrorMessageForForgotPasswordIdentity();
            $this->redirectOnError($inParam2);
            return;
        }
        $minimumPasswordLength = $forgotPasswordEngine->getMinimumPasswordLength();
        if($newPassword !== $confirmNewPassword) {
            $this->showErrorMessageForForgotPasswordNonMatch($minimumPasswordLength);
            $this->redirectOnError($inParam2);
            return;
        }
        if(! $forgotPasswordEngine->resetUsersPassword($forgotPassword1->getToken(), $forgotPassword2->getUserID(), $newPassword, $confirmNewPassword)) {
            $this->showErrorMessageForForgotPasswordNonMatch($minimumPasswordLength);
            $this->redirectOnError($inParam2);
            return;
        }
        $forgotPasswordEngine->removeForgotPassword($forgotPassword1);
        $this->showSuccessMessageForForgotPasswordChange();
        $this->response = Response::redirect(new Link("users/login"));
    }
    private function sendExternalAuthenticationEmail(User $user) {
        $mailTemplateEngine = MailTemplateEngine::getInstance();
        $mail = $mailTemplateEngine->loadTemplate("forgotPasswordExternalAuthentication");
        if($mail === false) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
        $mail->addRecipient($user->getEmail());
        $mail->setBulkMail(false);
        $mail->addReplacementValue("[[name]]", $user->getEmail(), $user->getFirstName());
        if(! $mail->sendMail()) {
            $this->showErrorMessageForForgotPassword();
            return;
        }
    }
    private function showErrorMessageForForgotPassword() {
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, "Sorry, something went wrong when I tried to generate a password reset token for you. If this keeps happening, please see an administrator."));
    }
    private function showErrorMessageForForgotPasswordIdentity() {
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, "Sorry, I couldn't validate your identity."));
    }
    private function showErrorMessageForForgotPasswordNonMatch($passwordLength) {
        if(! is_numeric($passwordLength)) {
            return;
        }
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, "Sorry, your chosen password didn't meet the minimum length requirement (passwords must have {$passwordLength} characters) or didn't match the confirmation password."));
    }
    private function redirectOnError($token) {
        $this->response = Response::redirect(new Link("users/forgotPassword/{$token}"));
    }
    private function showSuccessMessageForForgotPasswordChange() {
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::success, "Your password has been changed."));
    }
    public function getResponse() {
        return $this->response;
    }
}