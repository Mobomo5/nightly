<?php
//This file contains function that deal with the current user viewing the site.
require_once(DATABASE_OBJECT_FILE);
require_once(PASSWORD_FUNCTIONS_FILE);
require_once(HASHER_OBJECT_FILE);
require_once(SYSTEM_LOGGER_OBJET_FILE);
require_once(LINK_OBJECT_FILE);
require_once(USER_OBJECT_FILE);

class currentUser extends user {
    private $isLoggedIn;
    private $tempID;

    static function userIsInSession() {
        if (!isset($_SESSION['educaskCurrentUser'])) {
            return false;
        }
        return true;
    }

    static function getUserSession() {
        //if the user's object hasn't been created yet, create it
        if (!self::userIsInSession()) {
            self::setUserSession(new currentUser());
        }
        //return the user object
        return $_SESSION['educaskCurrentUser'];
    }

    static function setUserSession(currentUser $object) {
        //verify the variable given is a user object. If it is not, get out of here.
        if (get_class($object) != 'currentUser') {
            return;
        }
        $_SESSION['educaskCurrentUser'] = $object;
    }

    private static function destroySession() {
        unset($_SESSION['educaskCurrentUser']);
        $_SESSION['educaskCurrentUser'] = new currentUser();
    }

    public function __construct() {
        if (self::userIsInSession()) {
            self::getUserSession();
        }
        //Start a guest session
        $this->isLoggedIn = false;
        $this->tempID = null;
        $this->setRoleID(GUEST_ROLE_ID);
        $this->setGivenIdentifier(null);
        $this->setUserName(null);
        $this->setFirstName('Anonymous');
        $this->setLastName('Guest');
        $this->setEmail('anon@anon.ca');
    }

    public function getUserID() {
        return $this->tempID;
    }

    public function isLoggedIn() {
        return $this->isLoggedIn;
    }

    public function logIn($userName, $password) {
        if ($this->isLoggedIn) {
            return true;
        }
        $hookEngine = hookEngine::getInstance();
        $hookEngine->runAction('userLoggingIn');
        //repeated twice just in case a plugin logs the user in
        if ($this->isLoggedIn) {
            return true;
        }

        $perm = permissionEngine::getInstance()->getPermission('userCanLogIn');
        if (!permissionEngine::getInstance()->checkPermission($perm, $this->getRoleID())) {
            return false;
        }

        if (isset($_SESSION['userCanLogIn']) && $_SESSION['userCanLogIn'] == false) {
            return false;
        }

        $database = database::getInstance();
        $database->connect();

        if (!$database->isConnected()) {
            return false;
        }

        $userName = $database->escapeString($userName);

        $column = 'userID, roleID, userName, givenIdentifier, password, firstName, lastName, email';
        $table = 'user';
        $where = '((email = \'' . $userName . '\') OR (userName = \'' . $userName . '\') OR (givenIdentifier = \'' . $userName . '\'))';

        if ($database->isConnected()) {
            $results = $database->getData($column, $table, $where);
        } else {
            $results = null;
        }

        //If there weren't any accounts found or too many accounts found
        if ($results == null) {
            $hookEngine->runAction('userFailedToLogIn');
            return false;
        }
        if (count($results) > 1) {
            $hookEngine->runAction('userFailedToLogIn');
            return false;
        }

        $dbPassword = $results[0]['password'];
        $hasher = new hasher();
        if (!$hasher->verifyHash($password, $dbPassword)) {
            $hookEngine->runAction('userFailedToLogIn');
            unset($dbPassword);
            unset($results);
            unset($hasher);
            return false;
        }
        unset($dbPassword);
        foreach ($results as $result) {
            unset($result['password']);
        }
        unset($hasher);

        $this->isLoggedIn = true;
        $this->tempID = $results[0]['userID'];
        $this->setRoleID($results[0]['roleID']);
        $this->setFirstName($results[0]['firstName']);
        $this->setLastName($results[0]['lastName']);
        $this->setEmail($results[0]['email']);
        $this->setGivenIdentifier($results[0]['givenIdentifier']);
        $this->setUserName($results[0]['userName']);

        $database->updateTable('user', 'lastAccess = CURRENT_TIMESTAMP', 'userID=' . $this->tempID);
        self::setUserSession($this);
        $logEntry = new logEntry(1, logEntryType::info, 'A new session was opened for ' . $this->getFullName() . ', who has an IP of ' . $_SERVER['REMOTE_ADDR'] . '.', $this->getUserID());
        logger::getInstance()->logIt($logEntry);
        $hookEngine->runAction('userLoggedIn');
        return true;
    }

    public function logOut() {
        $hookEngine = hookEngine::getInstance();
        $hookEngine->runAction('userIsLoggingOut');

        //Destroy the current user session and create a new user object.
        self::destroySession();
        self::setUserSession(new currentUser());
        $hookEngine->runAction('userLoggedOut');
        header('Location: ' . new link(''));
    }

    public function toUser() {
        if (!$this->isLoggedIn) {
            return new user(1, GUEST_ROLE_ID, $this->getGivenIdentifier(), $this->getUserName(), $this->getFirstName(), $this->getLastName(), $this->getEmail());
        }
        return new user($this->tempID, $this->getRoleID(), $this->getGivenIdentifier(), $this->getUserName(), $this->getFirstName(), $this->getLastName(), $this->getEmail());
    }
}