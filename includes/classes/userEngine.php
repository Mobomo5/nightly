<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 14/05/14
 * Time: 4:21 PM
 */
require_once(USER_OBJECT_FILE);

class userEngine {

    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new userEngine();
        }
        return self::$instance;
    }

    private function __construct() {

    }

    public function getUser($inID) {

        if (!permissionEngine::getInstance()->currentUserCanDo('userCanGetUsersFromDB')) {
            return false;
        }

        $val = new validator('userID');

        if (!$val->validate($inID)) {
            return false;
        }

        $db = database::getInstance();
        $results = $db->getData('userID, userName, firstName, lastName, email, givenIdentifier, roleID, birthday', 'user', "userID = '$inID'");

        if (!$results) {
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
        $birthday = $results[0]['birthday'];

        $user = new user($userID, $roleID, $givenIdentifier, $userName, $firstName, $lastName, $email, $birthday);

        return $user;
    }

    public function setUser(user $inUser) {

        if (!permissionEngine::getInstance()->currentUserCanDo('userCanModifyUsersInDB')) {
            return false;
        }

        $db = database::getInstance();

        $userID = $db->escapeString($inUser->getUserID());
        $roleID = $db->escapeString($inUser->getRoleID());
        $firstName = $db->escapeString($inUser->getFirstName());
        $lastName = $db->escapeString($inUser->getLastName());
        $userName = $db->escapeString($inUser->getUserName());
        $givenID = $db->escapeString($inUser->getGivenIdentifier());
        $birthday = $db->escapeString($inUser->getBirthday());

        $results = $db->updateTable('user', "roleID = '$roleID', firstName= '$firstName' , lastName = '$lastName', userName='$userName', givenIdentifier='$givenID', birthday = '" . date("Y-m-d", $birthday) . "'", "userID = '$userID'");
        if (!$results) {
            return false;
        }

        return true;
    }

    /**
     * @param user $inUser
     * @param      $password
     * @return bool | int returns new user ID on success
     */
    public function addUser(user $inUser, $password) {
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanAddUsersToDB')) {
            return false;
        }
        $db = database::getInstance();

        // hash pass
        $hasher = new hasher();
        $pass = $hasher->generateHash($password);

        $roleID = $db->escapeString($inUser->getRoleID());
        $firstName = $db->escapeString($inUser->getFirstName());
        $lastName = $db->escapeString($inUser->getLastName());
        $userName = $db->escapeString($inUser->getUserName());
        $email = $db->escapeString($inUser->getEmail());
        $givenID = $db->escapeString($inUser->getGivenIdentifier());
        $birthday = date("Y-m-d", $db->escapeString($inUser->getBirthday()));
        $password = $db->escapeString($pass);

        $results = $db->insertData('user', 'roleID, firstName,lastName, userName, email, givenIdentifier, birthday, password',
            "'$roleID', '$firstName','$lastName', '$userName','$email', '$givenID', '$birthday', '$password'");

        if (!$results) {
            echo $db->getError();
            return false;
        }

        $results = $db->getData('userID', 'user', "firstName = '$firstName' AND lastName = '$lastName' AND userName = '$userName'");
        if (!$results) {
            echo $db->getError();

            return false;
        }

        $userID = $results[0]['userID'];
        return $userID;
    }

    public function deleteUser(user $userToBeDeleted) {
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanDeleteUsers')) {
            return false;
        }

        $userID = $userToBeDeleted->getUserID();
        $userName = $userToBeDeleted->getUserName();

        $db = database::getInstance();

        $userID = $db->escapeString($userID);
        $userName = $db->escapeString($userName);

        $results = $db->removeData('user', "userID = '$userID' AND userName = '$userName'");

        if (!$results) {
            return false;
        }

        return true;
    }

    public function updateUserPassword(user $inUser, $newPassword, $oldPassword) {

        if (!permissionEngine::getInstance()->currentUserCanDo('userCanUpdatePassword')) {
            return false;
        }

        if (strlen($newPassword) < 6) {
            return false;
        }

        $userID = $inUser->getUserID();
        $db = database::getInstance();
        $results = $db->getData('password', 'user', "userID = '$userID'");

        if (!$results) {
            return false;
        }
        $storedPassword = $results[0]['password'];

        $hasher = new hasher();
        if (!$hasher->verifyHash($oldPassword, $storedPassword)) {
            echo 'not the pass';
            return false;
        }
        $newHashed = $hasher->generateHash($newPassword);

        $results = $db->updateTable('user', "password = '$newHashed'", "userID = '$userID'");

        if (!$results) {
            echo $db->getError();
            return false;
        }

        return true;
    }

    public function getUserBio(user $inUser) {

        $userID = $inUser->getUserID();

        $db = database::getInstance();
        $results = $db->getData('bio', 'user', "userID = '$userID'");

        if (!$results) {
            return false;
        }

        return $results[0]['bio'];
    }
} 