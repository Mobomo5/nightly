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

//        if (!permissionEngine::getInstance()->currentUserCanDo('userCanGetUsersFromDB')){//@todo: add this perm to db
//            return false;
//        }

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

//        if (!permissionEngine::getInstance()->currentUserCanDo('userCanModifyUsersInDB')){//@todo: add this perm to db
//            return false;
//        }

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

    public function updateUserPassword(user $inUser, $hashedNewPassword, $hashedOldPassword) {

    }

    public function addUser(user $inUser, $hashedPassword) {
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanAddUsersToDB')) { //@todo: add this perm to db
            return false;
        }
        $db = database::getInstance();

        $roleID = $db->escapeString($inUser->getRoleID());
        $firstName = $db->escapeString($inUser->getFirstName());
        $lastName = $db->escapeString($inUser->getLastName());
        $userName = $db->escapeString($inUser->getUserName());
        $givenID = $db->escapeString($inUser->getGivenIdentifier());
        $birthday = $db->escapeString($inUser->getBirthday());

        return true;
    }

    public function deleteUser(user $userToBeDeleted) {
        return true;
    }
} 