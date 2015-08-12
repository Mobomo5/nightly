<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 30/04/14
 * Time: 12:22 PM
 */
class User {
    private $userID;
    private $userRole;
    private $givenIdentifier;
    private $userName;
    private $firstName;
    private $lastName;
    private $birthday;
    private $profilePictureLocation;
    private $email;
    private $active;
    private $isExternalAuthentication;
    public function __construct($inUserID, $inUserRole, $inGivenIdentifier, $inUserName, $inFirstName, $inLastName, $inEmail, Link $inProfilePictureLocation, DateTime $inBirthday = null, $inActive = true, $inIsExternalAuthentication = false) {
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
        if(! is_string($inFirstName)) {
            return;
        }
        if(! is_string($inLastName)) {
            return;
        }
        $validator = new emailValidator();
        if (!$validator->validate($inEmail)) {
            return;
        }
        if(! is_bool($inActive)) {
            return;
        }
        if(! is_bool($inIsExternalAuthentication)) {
            return;
        }
        $this->userID = $inUserID;
        $this->userRole = $inUserRole;
        $this->givenIdentifier = trim($inGivenIdentifier);
        $this->userName = strip_tags(trim($inUserName));
        $this->firstName = strip_tags(trim($inFirstName));
        $this->lastName = strip_tags(trim($inLastName));
        $this->email = $inEmail;
        $this->profilePictureLocation = $inProfilePictureLocation;
        $this->active = $inActive;
        $this->isExternalAuthentication = $inIsExternalAuthentication;
        if($inBirthday === null) {
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
        if($inGivenIdentifier === null) {
            $this->givenIdentifier = null;
            return;
        }
        if(! is_string($inGivenIdentifier)) {
            return;
        }
        $this->givenIdentifier = trim($inGivenIdentifier);
    }
    public function getUserName() {
        return $this->userName;
    }
    public function setUserName($inUserName) {
        if(! is_string($inUserName)) {
            return;
        }
        $this->userName = strip_tags(trim($inUserName));
    }
    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }
    public function getFirstName() {
        return $this->firstName;
    }
    public function setFirstName($inFirstName) {
        if(! is_string($inFirstName)) {
            return;
        }
        $this->firstName = strip_tags(trim($inFirstName));
    }
    public function getLastName() {
        return $this->lastName;
    }
    public function setLastName($inLastName) {
        if(! is_string($inLastName)) {
            return;
        }
        $this->lastName = strip_tags(trim($inLastName));
    }
    public function getBirthday() {
        return $this->birthday;
    }
    public function setBirthday(DateTime $inDate = null) {
        if($inDate === null) {
            $this->birthday = new DateTime('June 23, 1912');
            return;
        }
        $this->birthday = $inDate;
    }
    public function setProfilePictureLocation(Link $inLocation) {
        $this->profilePictureLocation = $inLocation;
    }
    public function getProfilePictureLocation() {
        $this->profilePictureLocation->togglePhysicalFile(true);
        return $this->profilePictureLocation;
    }
    public function getEmail() {
        return $this->email;
    }
    public function setEmail($inEmail) {
        $validator = new emailValidator();
        if (!$validator->validate($inEmail)) {
            return;
        }
        $this->email = $inEmail;
    }
    public function isActive() {
        return $this->active;
    }
    public function setIsActive($inActive = false) {
        if(! is_bool($inActive)) {
            return;
        }
        $this->active = $inActive;
    }
    public function isExternalAuthentication() {
        return $this->isExternalAuthentication;
    }
    public function setIsExternalAuthentication($inIsExternalAuthentication = false) {
        if(! is_bool($inIsExternalAuthentication)) {
            return;
        }
        $this->isExternalAuthentication = $inIsExternalAuthentication;
    }
}