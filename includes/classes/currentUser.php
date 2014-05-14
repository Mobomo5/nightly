<?php
//This file contains function that deal with the current user viewing the site.
require_once(DATABASE_OBJECT_FILE);
require_once(PASSWORD_FUNCTIONS_FILE);
require_once(HASHER_OBJECT_FILE);
require_once(SYSTEM_LOGGER_OBJET_FILE);
require_once(LINK_OBJECT_FILE);

class currentUser extends user{
    private $isLoggedIn;
    private $userID;
    private $userRole;
    private $givenIdentifier;
    private $userName;
    private $firstName;
    private $lastName;
    private $email;

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
        if (get_class($object) != "currentUser") {
            return;
        }
        $_SESSION['educaskCurrentUser'] = $object;
    }
    private static function destroySession() {
        $_SESSION['educaskCurrentUser'] = new user();
        unset($_SESSION['educaskCurrentUser']);
    }

    public function __construct() {
        //Start a guest session
        $this->isLoggedIn = false;
        $this->userID = null;
        $this->userRole = GUEST_ROLE_ID;
        $this->firstName = 'Anonymous';
        $this->lastName = 'Guest';
    }

    public function isLoggedIn() {
        return $this->isLoggedIn;
    }

    public function getUserRole() {
        return $this->userRole;
    }

    public function getRoleID() {
        return $this->userID;
    }

    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
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

        if (!isset($_SESSION['userCanLogIn'])) {
            return false;
        }
        if($_SESSION['userCanLogIn'] == false) {
            return false;
        }

        $database = database::getInstance();
        $database->connect();

        if (!$database->isConnected()) {
            return false;
        }

        $userName = $database->escapeString($userName);


        $column = 'userID, roleID, userName, givenIdentifier, password, firstName, lastName, givenIdentifier, email';
        $table = 'user';
        $where = 'WHERE ((email = \'' . $userName . '\') OR (userName = \'' . $userName . '\') OR (givenIdentifier = \'' . $userName . '\'))';


        if ($database->isConnected()) {
            $results = $database->getData($column, $table, $where);
        } else {
            $results = NULL;
        }

        //If there weren't any accounts found or too many accounts found
        if ($results == NULL) {
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
        $this->userID = $results[0]['userID'];
        $this->userRole = $results[0]['roleID'];
        $this->firstName = $results[0]['firstName'];
        $this->lastName = $results[0]['lastName'];

        $database->updateTable('user', 'lastAccess = CURRENT_TIMESTAMP', 'userID = ' . $this->userID);
        self::setUserSession($this);
        $logger = logger::getInstance();
        $logger->logIt($this->userID, 'A new session was opened for ' . $this->getFullName() . ', who has an IP of ' . $_SERVER['REMOTE_ADDR'] . '.');
        $hookEngine->runAction('userLoggedIn');
        return true;
    }

    public function logOut() {
        $hookEngine = hookEngine::getInstance();
        $hookEngine->runAction('userLoggingOut');

        //Destroy the current user session and create a new user object.
        self::destroySession();
        self::setUserSession(new currentUser());
        $hookEngine->runAction('userLoggedOut');
        header('Location: ' . new link(''));
    }
}