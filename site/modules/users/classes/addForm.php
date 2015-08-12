<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class addForm implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(count($request->getParameters(true)) > 2) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! PermissionEngine::getInstance()->currentUserCanDo("canAddUsers")) {
            $this->response = Response::fourOhThree();
            return;
        }
        if($request->isPostRequest()) {
            $this->response = $this->addUser();
            return;
        }
        $user = new User(0, GUEST_ROLE_ID, "", "", "", "", "example@example.ca", new Link('images/defaultUserPicture.png', true), new DateTime("1993-04-30"), true, false);
        $model = array('user'=>$user, 'roles'=>$this->getPossibleUserRoles(), 'postLink'=>new Link('users/add'), 'showPasswordFields'=>true);
        $this->response = new Response(200, "@users/userForm.twig", "Add a User", "user", $model);
    }
    private function getPossibleUserRoles() {
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return array();
        }
        $userRolesRaw = $database->getData("roleID, roleName", "role");
        if(! $userRolesRaw) {
            return array();
        }
        return $userRolesRaw;
    }
    private function addUser() {
        if(! AntiForgeryToken::getInstance()->validate()) {
            return Response::fiveHundred();
        }
        if(! Honeypot::getInstance()->validate()) {
            return Response::fiveHundred();
        }
        $firstName = Request::getPostParameter("firstName");
        $lastName = Request::getPostParameter("lastName");
        $userName = Request::getPostParameter("userName");
        $email = Request::getPostParameter("email");
        $userRole = Request::getPostParameter("userRole");
        $password = Request::getPostParameter("password");
        $passwordConfirmation = Request::getPostParameter("passwordConfirmation");
        $active = Request::getPostParameter("active");
        $givenIdentifier = Request::getPostParameter("givenIdentifier");
        $birthday = Request::getPostParameter("birthday");
        $profilePictureDefault = Request::getPostParameter("profilePictureDefault");
        if($active === "1") {
            $active = true;
        } else {
            $active = false;
        }
        if($givenIdentifier === false) {
            $givenIdentifier = null;
        }
        if($givenIdentifier === "") {
            $givenIdentifier = null;
        }
        if($birthday === false) {
            $birthday = new DateTime();
        } else {
            $birthday = new DateTime($birthday);
        }
        require_once(EDUCASK_ROOT . "/site/modules/users/classes/profilePicture.php");
        $profilePictureLocation = profilePicture::uploadProfilePicture(new Link($profilePictureDefault, true));
        $newUser = new User(0, $userRole, $givenIdentifier, $userName, $firstName, $lastName, $email, $profilePictureLocation, $birthday, $active, false);
        $newUser->setRoleID($userRole);
        $newUser->setGivenIdentifier($givenIdentifier);
        $newUser->setUserName($userName);
        $newUser->setFirstName($firstName);
        $newUser->setLastName($lastName);
        $newUser->setEmail($email);
        $newUser->setProfilePictureLocation($profilePictureLocation);
        $newUser->setBirthday($birthday);
        $newUser->setIsActive($active);
        $message = "Sorry, something went wrong when I tried to add the account.";
        $model = array('user'=>$newUser, 'roles'=>$this->getPossibleUserRoles(), 'postLink'=>new Link('users/add'), 'showPasswordFields'=>true);
        if($newUser->getUserID() !== 0) {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
            return new Response(200, "@users/userForm.twig", "Add a User", "user", $model);
        }
        if($newUser->getUserName() === "") {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
            return new Response(200, "@users/userForm.twig", "Add a User", "user", $model);
        }
        if($newUser->getFirstName() === "") {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
            return new Response(200, "@users/userForm.twig", "Add a User", "user", $model);
        }
        if(! is_string($password)) {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
            return new Response(200, "@users/userForm.twig", "Add a User", "user", $model);
        }
        if(strlen($password) < UserEngine::getInstance()->getMinimumPasswordLength()) {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, "The chosen password isn't long enough."));
            return new Response(200, "@users/userForm.twig", "Add a User", "user", $model);
        }
        if($password !== $passwordConfirmation) {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, "The chosen password doesn't match the password confirmation."));
            return new Response(200, "@users/userForm.twig", "Add a User", "user", $model);
        }
        $result = UserEngine::getInstance()->addUser($newUser, $password);
        if(! $result) {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
            return new Response(200, "@users/userForm.twig", "Add a User", "user", $model);
        }
        $database = Database::getInstance();
        $userID = $database->getLastInsertID();
        $message = "The user {$newUser->getFullName()} has been added.";
        Logger::getInstance()->logIt(new LogEntry(0, logEntryType::info, $message, $userID, new DateTime()));
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::success, $message));
        return Response::redirect(new Link("users/add"));
    }
    public function getResponse() {
        return $this->response;
    }
}