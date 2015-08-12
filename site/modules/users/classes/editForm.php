<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class editForm implements IModule {
    private $response;
    public function __construct(Request $request) {
        $parameters = $request->getParameters(true);
        if(count($parameters) > 3) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! isset($parameters[2])) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! is_numeric($parameters[2])) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! PermissionEngine::getInstance()->currentUserCanDo("canEditUsers")) {
            $this->response = Response::fourOhThree();
            return;
        }
        $userEngine = UserEngine::getInstance();
        $user = $userEngine->getUser($parameters[2]);
        if(! $user) {
            $this->response = Response::fourOhFour();
            return;
        }
        if($request->isPostRequest()) {
            $this->response = $this->doEditUser($user);
            return;
        }
        $model = array('user'=>$user, 'roles'=>$this->getPossibleUserRoles(), 'postLink'=>new Link('users/edit/'.$parameters[2]),'showPasswordFields'=>false);
        $this->response = new Response(200, "@users/userForm.twig", "Edit {$user->getFirstName()}'s Account", "user", $model);
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
    private function doEditUser(User $user) {
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
        $user->setRoleID($userRole);
        $user->setGivenIdentifier($givenIdentifier);
        $user->setUserName($userName);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setProfilePictureLocation($profilePictureLocation);
        $user->setBirthday($birthday);
        $user->setIsActive($active);
        $message = "Sorry, something went wrong when I tried to edit the account.";
        $model = array('user'=>$user, 'roles'=>$this->getPossibleUserRoles(), 'postLink'=>new Link("users/edit/" . $user->getUserID()), 'showPasswordFields'=>false);
        if($user->getUserName() === "") {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
            return new Response(200, "@users/userForm.twig", "Edit {$user->getFirstName()}'s Account", "user", $model);
        }
        if($user->getFirstName() === "") {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
            return new Response(200, "@users/userForm.twig", "Edit {$user->getFirstName()}'s Account", "user", $model);
        }
        $result = UserEngine::getInstance()->setUser($user);
        if(! $result) {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
            return new Response(200, "@users/userForm.twig", "Edit {$user->getFirstName()}'s Account", "user", $model);
        }
        $message = "The user {$user->getFullName()} has been modified.";
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::success, $message));
        return Response::redirect(new Link("users/edit/" . $user->getUserID()));
    }
    public function getResponse() {
        return $this->response;
    }
}