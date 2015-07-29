<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class changePasswordForm implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(count($request->getParameters(true)) > 2) {
            $this->response = Response::fourOhFour();
            return;
        }
        session_regenerate_id(true);
        $currentUser = CurrentUser::getUserSession();
        if(!$currentUser->isLoggedIn()) {
            $this->response = Response::fourOhFour();
            return;
        }
        if($currentUser->isExternalAuthentication()) {
            $this->response = new Response(200, "@users/cantChangePassword.twig", "Change your Password", "user");
            return;
        }
        if($request->isPostRequest()) {
            $this->response = $this->doChangePassword();
            return;
        }
        $this->response = new Response(200, "@users/changePassword.twig", "Change your Password", "user");
    }
    private function doChangePassword() {
        if(! AntiForgeryToken::getInstance()->validate()) {
            return Response::fiveHundred();
        }
        if(! Honeypot::getInstance()->validate()) {
            return Response::fiveHundred();
        }
        $currentPassword = Request::getPostParameter("currentPassword");
        $newPassword = Request::getPostParameter("newPassword");
        $passwordConfirmation = Request::getPostParameter("passwordConfirmation");
        $fieldMessage = "All fields in this form are required.";
        if(! $currentPassword) {
            return $this->showErrorMessage($fieldMessage);
        }
        if(! $newPassword) {
            return $this->showErrorMessage($fieldMessage);
        }
        if(! $passwordConfirmation) {
            return $this->showErrorMessage($fieldMessage);
        }
        if($newPassword !== $passwordConfirmation) {
            return $this->showErrorMessage("The password confirmation didn't match the chosen new password.");
        }
        $minimumPasswordLength = VariableEngine::getInstance()->getVariable("minimumPasswordLength");
        if(! $minimumPasswordLength) {
            $minimumPasswordLength = 5;
        } else {
            $minimumPasswordLength = (int) $minimumPasswordLength->getValue();
        }
        if(strlen($newPassword) < $minimumPasswordLength) {
            return $this->showErrorMessage("Sorry: your new password wasn't long enough.");
        }
        $user = CurrentUser::getUserSession();
        $generalError = "Sorry, I encounterd an error while trying to change your password.";
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return $this->showErrorMessage($generalError);
        }
        $userID = $user->getUserID();
        if(! is_numeric($userID)) {
            return $this->showErrorMessage($generalError);
        }
        $userID = $database->escapeString($userID);
        $passwordRaw = $database->getData("password", "user", "userID={$userID}");
        if(! $passwordRaw) {
            return $this->showErrorMessage($generalError);
        }
        if(count($passwordRaw) > 1) {
            return $this->showErrorMessage($generalError);
        }
        if(! Hasher::verifyHash($currentPassword, $passwordRaw[0]['password'])) {
            return $this->showErrorMessage("Sorry: I couldn't validate your identity.");
        }
        $newPasswordHashed = Hasher::generateHash($newPassword);
        if(! $newPasswordHashed) {
            return $this->showErrorMessage($generalError);
        }
        $result = $database->updateTable("user", "password='{$newPasswordHashed}'", "userID={$userID}");
        if(! $result) {
            return $this->showErrorMessage($generalError);
        }
        NoticeEngine::getInstance()->addNotice(new Notice("success", "Your password has been changed."));
        return Response::redirect(new Link("users/changePassword"));
    }
    public function showErrorMessage($message) {
        NoticeEngine::getInstance()->addNotice(new Notice("warning", $message));
        return Response::redirect(new Link("users/changePassword"));
    }
    public function getResponse() {
        return $this->response;
    }
}