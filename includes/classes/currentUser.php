<?php
//This file contains function that deal with the current user viewing the site.
require_once(DATABASE_OBJECT_FILE);
require_once(PASSWORD_FUNCTIONS_FILE);

class currentUser {
    private $isLoggedIn;
    private $userID;
    private $userRole;
    private $firstName;
    private $lastName;
    static function userIsInSession() {
        if (!isset($_SESSION['educaskCurrentUser'])) {
            return false;
        }
        return true;
    }
    static function getUserSession(){
        //if the user's object hasn't been created yet, create it
        if (! self::userIsInSession()) {
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
        $_SESSION['educaskUser'] = $object;
    }
    private function __construct() {
        //Start a guest session
        $this->isLoggedIn = false;
        $this->userID = null;
        $this->userRole = GUEST_ROLE_ID;
        $this->firstName = 'Guest';
        $this->lastName = null;
    }
    public function isLoggedIn() {
        return $this->isLoggedIn;
    }
    public function getUserRole() {
        return $this->userRole;
    }
    public function getUserID() {
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
        CRYPT_BLOWFISH or die('No Blowfish Found');
        $database = new database();
        $database->connect();

        if(!$database->isConnected()) {
            return false;
        }

        $hashedPassword = generateHash($password);
        $userName = mysqli_real_escape_string($database->getDatabaseConnection(), $userName);


        $column = 'userID, roleID, password, firstName, lastName';
        $table = 'users';
        $where = 'WHERE email = \'' . $userName . '\'';


        if($database->isConnected()) {
            $results = $database->getData($column, $table, $where);
        } else {
            $results = null;
        }

        //If there weren't any accounts found or too many accounts found
        if($results == null) {
            return false;
        }

        if(count($results) > 1) {
            return false;
        }

        $dbPassword = $results[0]['password'];
        if(!verifyHash($password, $dbPassword)) {
            return false;
        }

        $this->isLoggedIn = true;
        $this->userID = $results[0]['userID'];
        $this->userRole = $results[0]['roleID'];
        $this->firstName = $results[0]['firstName'];
        $this->lastName = $results[0]['lastName'];

        $database->disconnect();
        self::setUserSession($this);
        return true;
    }

    public function logOut() {
        //reset all variables to default
        $this->isLoggedIn = false;
        $this->userID = null;
        $this->userRole = $this->guestRoleID;
        $this->firstName = 'Guest';
        $this->lastName = null;

        //Save the user object
        self::setUserSession($this);
    }
}

/** EVALUATE LATER ***************
 *
 * public function hasPermission($inPermissionName)
 * {
 * //make sure the user has a role
 * if ($this->userRole == NULL) {
 * return false;
 * }
 *
 * //connect to the database
 * $database = new database();
 * $database->connect();
 *
 * //prepare the Query string. the canDo column contains a 0 or a 1 indicating if a role can do that action or not.
 * $column = 'canDo';
 * $table = 'permission p, permissionSet ps';
 * $where = 'WHERE p.permissionID = ps.permissionID AND p.permissionName = \'' . $inPermissionName . '\' AND ps.roleID = \'' . $this->userRole . '\' AND ps.canDo = 1';
 *
 * //get the value from the database
 * $hasPermission = $database->getData($column, $table, $where);
 *
 * $database->disconnect();
 *
 * //If all data fields have been filtered out, the user doesn't have permission
 * if ($hasPermission == NULL) {
 * return false;
 * }
 *
 * //By process of elimination, the user has permission
 * return true;
 * }
 */
?>

