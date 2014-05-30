<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 30/04/14
 * Time: 12:22 PM
 */
require_once(VALIDATOR_OBJECT_FILE);

class user {
    private $userID;
    private $userRole;
    private $givenIdentifier;
    private $userName;
    private $firstName;
    private $lastName;
    private $birthday;
    private $email;

    public function __construct($inUserID, $inUserRole, $inGivenIdentifier, $inUserName, $inFirstName, $inLastName, $inEmail, $inBirthday) {
        if (!is_numeric($inUserID)) {
            return;
        }
        if ($inUserID < 0) {
            return;
        }
        if (!is_numeric($inUserRole)) {
            return;
        }
        if ($inUserRole < 1) {
            return;
        }
        $validator = new validator('email');
        if (!$validator->validate($inEmail)) {
            return;
        }

        $this->userID = $inUserID;
        $this->userRole = $inUserRole;
        $this->givenIdentifier = $inGivenIdentifier;
        $this->userName = $inUserName;
        $this->firstName = $inFirstName;
        $this->lastName = $inLastName;
        $this->email = $inEmail;
        $this->birthday = $inBirthday;
    }

    public function getUserID() {
        return $this->userID;
    }

    public function getRoleID() {
        return $this->userRole;
    }

    public function setRoleID($inRoleID) {
        if (!is_numeric($inRoleID)) {
            return;
        }
        if ($inRoleID < 1) {
            return;
        }
        $this->userRole = $inRoleID;
    }

    public function getGivenIdentifier() {
        return $this->givenIdentifier;
    }

    public function setGivenIdentifier($inGivenIdentifier) {
        $this->givenIdentifier = $inGivenIdentifier;
    }

    public function getUserName() {
        return $this->userName;
    }

    public function setUserName($inUserName) {
        $this->userName = $inUserName;
    }

    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($inFirstName) {
        $this->firstName = $inFirstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getBirthday() {
        return $this->birthday;
    }

    public function setLastName($inLastName) {
        $this->lastName = $inLastName;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($inEmail) {
        $validator = new validator('email');
        if (!$validator->validate($inEmail)) {
            return;
        }
        $this->email = $inEmail;
    }

    public function __toString() {
        return 'User: ' . $this->getFullName() . '<br/>User ID is ' . $this->userID . '<br/>User name is: ' . $this->userName . '<br/>Email is ' . $this->email . '<br/>Given ID is ' . $this->givenIdentifier . '<br/>User role is ' . $this->userRole . '<br/>';
    }
}