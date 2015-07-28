<?php
class CurrentUser extends User {
    private $isLoggedIn;
    static function userIsInSession() {
        if(! isset($_SESSION['educaskCurrentUser'])) {
            return false;
        }
        return true;
    }
    static function getUserSession() {
        //if the user's object hasn't been created yet, create it
        if (!self::userIsInSession()) {
            self::setUserSession(new CurrentUser());
        }
        return $_SESSION['educaskCurrentUser'];
    }
    static function setUserSession(CurrentUser $object) {
        $_SESSION['educaskCurrentUser'] = $object;
    }
    private static function destroySession() {
        unset($_SESSION['educaskCurrentUser']);
    }
    public function __construct($inUserID = 0, $inUserRole = GUEST_ROLE_ID, $inGivenIdentifier = 'anonymous', $inUserName = 'anonymous', $inFirstName = 'Anonymous', $inLastName = 'Guest', $inEmail = 'anon@anon.ca', Link $inProfilePictureLocation = null, DateTime $inBirthday = null, $isActive = true, $isExternalAuthentication = false, $isLoggedIn = false) {
        if($inProfilePictureLocation === null) {
            $inProfilePictureLocation = new Link('images/defaultUserPicture.png');
        }
        if(! is_bool($isLoggedIn)) {
            return;
        }
        $this->isLoggedIn = $isLoggedIn;
        parent::__construct($inUserID, $inUserRole, $inGivenIdentifier, $inUserName, $inFirstName, $inLastName, $inEmail, $inProfilePictureLocation, $inBirthday, $isActive, $isExternalAuthentication);
    }
    public function isLoggedIn() {
        if($this->getUserID() == 0) {
            return false;
        }
        return $this->isLoggedIn;
    }
    public function logIn($userName, $password) {
        if(! is_string($userName)) {
            return false;
        }
        if(! is_string($password)) {
            return false;
        }
        if ($this->isLoggedIn) {
            return true;
        }
        if (LockoutEngine::getInstance()->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            return false;
        }
        $database = Database::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            return false;
        }
        $userName = $database->escapeString(trim($userName));
        $column = 'userID, roleID, userName, givenIdentifier, password, firstName, lastName, email, profilePictureLocation, birthday, active, isExternalAuthentication';
        $table = 'user';
        $where = 'active=1 AND isExternalAuthentication=0 AND ((email = \'' . $userName . '\') OR (userName = \'' . $userName . '\') OR (givenIdentifier = \'' . $userName . '\'))';
        if ($database->isConnected()) {
            $results = $database->getData($column, $table, $where);
        } else {
            $results = null;
        }
        //If there weren't any accounts found or too many accounts found
        if ($results === null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        $dbPassword = $results[0]['password'];
        if (!Hasher::verifyHash($password, $dbPassword)) {
            return false;
        }
        self::setUserSession(new CurrentUser($results[0]['userID'], $results[0]['roleID'], $results[0]['givenIdentifier'], $results[0]['userName'], $results[0]['firstName'], $results[0]['lastName'], $results[0]['email'], new Link($results[0]['profilePictureLocation'], true), new DateTime($results[0]['birthday']), true, false, true));
        $this->isLoggedIn = true;
        $userID = $database->escapeString($this->getUserID());
        $database->updateTable('user', 'lastAccess = CURRENT_TIMESTAMP', "userID={$userID}");
        return true;
    }
    public function logOut() {
        //Destroy the current user session and create a new user object.
        self::destroySession();
        self::setUserSession(new CurrentUser());
        $this->isLoggedIn = false;
    }
}