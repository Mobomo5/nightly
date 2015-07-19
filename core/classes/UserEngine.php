<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 14/05/14
 * Time: 4:21 PM
 */
class UserEngine {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new UserEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing.
    }
    public function getUser($inID) {
        if(! is_numeric($inID)) {
            return false;
        }
        if($inID < 0) {
            return false;
        }
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $inID = $db->escapeString($inID);
        $results = $db->getData('userID, userName, firstName, lastName, email, givenIdentifier, roleID, profilePictureLocation, birthday', 'user', "userID = $inID");
        if (!$results) {
            return false;
        }
        if($results === null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        $userID = $results[0]['userID'];
        $userName = $results[0]['userName'];
        $firstName = $results[0]['firstName'];
        $lastName = $results[0]['lastName'];
        $email = $results[0]['email'];
        $givenIdentifier = $results[0]['givenIdentifier'];
        $roleID = $results[0]['roleID'];
        $profilePictureLocation = new Link($results[0]['profilePictureLocation'], true);
        $birthday = new DateTime($results[0]['birthday']);
        $user = new User($userID, $roleID, $givenIdentifier, $userName, $firstName, $lastName, $email, $profilePictureLocation, $birthday);
        return $user;
    }
    public function getUserByUsername($inUserName) {
        $inUserName = preg_replace('/\s+/', '', strip_tags($inUserName));
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $inUserName = $db->escapeString($inUserName);
        $results = $db->getData('userID', 'user', "userName = '{$inUserName}'");
        if (!$results) {
            return false;
        }
        if($results === null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        return $this->getUser($results[0]['userID']);
    }
    public function getUserByEmail($inEmail) {
        $inEmail = preg_replace('/\s+/', '', strip_tags($inEmail));
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $inEmail = $db->escapeString($inEmail);
        $results = $db->getData('userID', 'user', "email = '{$inEmail}'");
        if (!$results) {
            return false;
        }
        if($results === null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        return $this->getUser($results[0]['userID']);
    }
    public function setUser(User $inUser) {
        if (!PermissionEngine::getInstance()->currentUserCanDo('userCanModifyUsers')) {
            return false;
        }
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $userID = $db->escapeString($inUser->getUserID());
        $roleID = $db->escapeString($inUser->getRoleID());
        $firstName = $db->escapeString($inUser->getFirstName());
        $lastName = $db->escapeString($inUser->getLastName());
        $userName = $db->escapeString($inUser->getUserName());
        $givenID = $db->escapeString($inUser->getGivenIdentifier());
        $birthday = $inUser->getBirthday();
        $birthday = $db->escapeString($birthday->format("Y-m-d H:i:s"));
        $profilePictureLocation = $db->escapeString($inUser->getProfilePictureLocation()->getRawHref());
        $results = $db->updateTable('user', "roleID = $roleID, firstName= '$firstName' , lastName = '$lastName', userName='$userName', givenIdentifier='$givenID', birthday = '" . $birthday . "', profilePictureLocation = '$profilePictureLocation'", "userID = $userID");
        if (!$results) {
            return false;
        }
        return true;
    }
    /**
     * @param User $inUser
     * @param      $password
     * @return bool | int returns new user ID on success
     */
    public function addUser(User $inUser, $password) {
        if (!PermissionEngine::getInstance()->currentUserCanDo('userCanAddUsers')) {
            return false;
        }
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $pass = Hasher::generateHash($password);
        $roleID = $db->escapeString($inUser->getRoleID());
        $firstName = $db->escapeString($inUser->getFirstName());
        $lastName = $db->escapeString($inUser->getLastName());
        $userName = $db->escapeString($inUser->getUserName());
        $email = $db->escapeString($inUser->getEmail());
        $givenID = $db->escapeString($inUser->getGivenIdentifier());
        $birthday = $inUser->getBirthday();
        $birthday = $db->escapeString($birthday->format("Y-m-d H:i:s"));
        $picture = $db->escapeString($inUser->getProfilePictureLocation()->getRawHref());
        $password = $db->escapeString($pass);
        $results = $db->insertData('user', 'roleID, firstName,lastName, userName, email, givenIdentifier, birthday, profilePictureLocation, password',
            "$roleID, '$firstName','$lastName', '$userName','$email', '$givenID', '$birthday', '$picture', '$password'");
        if (!$results) {
            return false;
        }
        return true;
    }
    //@ToDO: This won't work since the user's id is tied with a lot of other data in the db. Darn Foreign Keys! :)
    public function deleteUser(User $userToBeDeleted) {
        if (!PermissionEngine::getInstance()->currentUserCanDo('userCanDeleteUsers')) {
            return false;
        }
        $userID = $userToBeDeleted->getUserID();
        $userName = $userToBeDeleted->getUserName();
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $userID = $db->escapeString($userID);
        $userName = $db->escapeString($userName);
        $results = $db->removeData('user', "userID = {$userID} AND userName = '{$userName}'");
        if (!$results) {
            return false;
        }
        return true;
    }
    public function updateUserPassword(User $inUser, $newPassword, $oldPassword) {
        if (!PermissionEngine::getInstance()->currentUserCanDo('userCanUpdatePassword')) {
            return false;
        }
        if (strlen($newPassword) < $this->getMinimumPasswordLength()) {
            return false;
        }
        $userID = $inUser->getUserID();
        if(! is_numeric($userID)) {
            return false;
        }
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $userID = $db->escapeString($userID);
        $results = $db->getData('password', 'user', "userID = $userID");
        if($results === false) {
            return false;
        }
        if($results === null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $storedPassword = $results[0]['password'];
        if (!Hasher::verifyHash($oldPassword, $storedPassword)) {
            return false;
        }
        $newHashed = Hasher::generateHash($newPassword);
        $newHashed = $db->escapeString($newHashed);
        $results = $db->updateTable('user', "password = '$newHashed'", "userID = $userID");
        if (!$results) {
            return false;
        }
        return true;
    }
    public function getUserBio(User $inUser) {
        $userID = $inUser->getUserID();
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $userID = $db->escapeString($userID);
        $results = $db->getData('bio', 'user', "userID = $userID");
        if (!$results) {
            return 'The bio is temporarily unavailable. Check back soon!';
        }
        if($results === null) {
            return 'No bio yet!';
        }
        if(count($results) > 1) {
            return 'No bio yet!';
        }
        if ($results[0]['bio'] === '') {
            return 'No bio yet!';
        }
        return $results[0]['bio'];
    }
    public function getMinimumPasswordLength() {
        $variableEngine = VariableEngine::getInstance();
        $minimumPasswordLength = $variableEngine->getVariable('minimumPasswordLength');
        $default = 10;
        if($minimumPasswordLength === null) {
            return $default;
        }
        if($minimumPasswordLength === false) {
            return $default;
        }
        if(! is_numeric($minimumPasswordLength->getValue())) {
            return $default;
        }
        return intval($minimumPasswordLength->getValue());
    }
} 