<?php
//This file contains function that deal with the current user viewing the site.
require_once(DATABASE_OBJECT_FILE);
require_once(PASSWORD_FUNCTIONS_FILE);

class user
{

    private $isLoggedIn;
    private $userID;
    private $userRole;
    private $firstName;
    private $lastName;
    private $guestRoleID = 4;

    public function __construct()
    {
        //prevent overwriting of the user's session if a new user object is called when it shouldn't be
        if (userIsInSession()) {
            $currentUser = getUserSession();

            $this->isLoggedIn = $currentUser->isLoggedIn();
            $this->userID = $currentUser->getUserID();
            $this->userRole = $currentUser->getUserRole();
            $this->firstName = $currentUser->getFirstName();
            $this->lastName = $currentUser->getLastName();

            //Save the user object
            setUserSession($this);

            return;
        }

        //Start a guest session
        $this->isLoggedIn = false;
        $this->userID = NULL;
        $this->userRole = $this->guestRoleID;
        $this->firstName = 'Guest';
        $this->lastName = NULL;

        //Save the user object
        setUserSession($this);
    }

    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }

    public function getUserRole()
    {
        return $this->userRole;
    }

    public function getUserID()
    {
        return $this->userID;
    }

    public function logIn($userName, $password)
    {
        CRYPT_BLOWFISH or die('No Blowfish Found');
        $database = new database();
        $database->connect();

        if (!$database->isConnected()) {
            return false;
        }

        $hashedPassword = generateHash($password);
        $userName = mysqli_real_escape_string($database->getDatabaseConnection(), $userName);


        $column = 'userID, roleID, password, firstName, lastName';
        $table = 'users';
        $where = 'WHERE email = \'' . $userName . '\'';


        if ($database->isConnected()) {
            $results = $database->getData($column, $table, $where);
        } else {
            $results = NULL;
        }

        //If there weren't any accounts found or too many accounts found
        if ($results == NULL) {
            return false;
        }

        if (count($results) > 1) {
            return false;
        }

        $dbPassword = $results[0]['password'];
        if (!verifyHash($password, $dbPassword)) {
            return false;
        }

        $this->isLoggedIn = true;
        $this->userID = $results[0]['userID'];
        $this->userRole = $results[0]['roleID'];
        $this->firstName = $results[0]['firstName'];
        $this->lastName = $results[0]['lastName'];

        $database->disconnect();
        setUserSession($this);
        return true;
    }

    public function logOut()
    {
        //reset all variables to default
        $this->isLoggedIn = false;
        $this->userID = NULL;
        $this->userRole = $this->guestRoleID;
        $this->firstName = 'Guest';
        $this->lastName = NULL;

        //Save the user object
        setUserSession($this);
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }
}

/** EVALUATE LATER ***************
 *
 *functions that help control the session
 *
 * function userIsInSession()
 * {
 * if (!isset($_SESSION['educaskUser'])) {
 * return false;
 * }
 * return true;
 * }
 *
 * function getUserSession()
 * {
 * //if the user's object hasn't been created yet, create it
 * if (!userIsInSession()) {
 * return new user();
 * }
 *
 * //return the user object
 * return $_SESSION['educaskUser'];
 * }
 *
 * function setUserSession(user $object)
 * {
 * //verify the variable given is a user object. If it is not, get out of here.
 * if (get_class($object) != "user") {
 * return;
 * }
 *
 * $_SESSION['educaskUser'] = $object;
 * }
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

