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
    public function __construct($inUserID, $inUserRole, $inGivenIdentifier, $inUserName, $inFirstName, $inLastName, $inEmail, DateTime $inBirthday = null) {
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
        $this->userName = strip_tags($inUserName);
        $this->firstName = strip_tags($inFirstName);
        $this->lastName = strip_tags($inLastName);
        $this->email = $inEmail;
        if($inBirthday == null) {
            $this->birthday = new DateTime('June 23, 1912');
            return;
        }
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
        $this->userName = strip_tags($inUserName);
    }
    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }
    public function getFirstName() {
        return $this->firstName;
    }
    public function setFirstName($inFirstName) {
        $this->firstName = strip_tags($inFirstName);
    }
    public function getLastName() {
        return $this->lastName;
    }
    public function setLastName($inLastName) {
        $this->lastName = strip_tags($inLastName);
    }
    public function getBirthday() {
        return $this->birthday;
    }
    public function setBirthday(DateTime $inDate = null) {
        if($inDate == null) {
            $this->birthday = new DateTime('June 23, 1912');
            return;
        }
        $this->birthday = $inDate;
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
}