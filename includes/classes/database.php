<?php
require_once(DATABASE_INTERFACE_FILE);

/**
 * Created by JetBrains PhpStorm.
 * User: Craig
 * Date: 4/10/14
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */
class database implements databaseInterface
{

    /**
     * @var
     */
    private $dbUsername;
    private $dbPassword;
    private $db;
    private $dbServer;
    private $dbObject;
    private $dbType;

    public static function getInstance()
    {

    }

    function isConnected()
    {
        if (empty($this->dbObject)) {
            return false;
        }
        return $this->dbObject->isConnected();
    }

    function __construct($inParameters = "")
    {

        require_once(EDUCASK_ROOT . '/includes/config.php');
        $this->dbUsername = $dbUserName;
        $this->dbPassword = $dbPassword;
        $this->db = 'educaskOld';
        $this->dbServer = $dbServer;
        $this->dbType = $dbType;

        // Dynamically create the new database object, if possible.
        if (!require_once(EDUCASK_ROOT . "/includes/databases/" . $this->dbType . ".php")) {
            // @todo: ERROR the file isn't there.

        }

        try {

            $this->dbObject = new $dbType;
            $this->dbObject->connect($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db);

        } catch (Exception $e) {
            printf("%s = %s", $e->getCode(), $e->getMessage());
            exit;
        }
    }

    function __destruct()
    {
        $this->disconnect();
    }

    function disconnect()
    {
        $this->dbObject->disconnect();
    }

    function select($select, $from, $where)
    {
        try {
            $result = $this->dbObject->select($select, $from, $where);

        } catch (Exception $e) {
            printf("%s = %s", $e->getCode(), $e->getMessage());
            exit;
        }

        return $result;
    }

    function query($inQuery)
    {
        try {
            $result = $this->dbObject->query($inQuery);

        } catch (Exception $e) {
            printf("%s = %s", $e->getCode(), $e->getMessage());
            exit;
        }

        return $result;
    }

    function insert($into, $columns, $values)
    {
        // TODO: Implement insert() method.
    }

    function update($table, $set, $values)
    {
        // TODO: Implement update() method.
    }

    function getUserByName($firstName, $lastName)
    {
        try {
            $result = $this->dbObject->getUserByName($firstName, $lastName);

        } catch (Exception $e) {
            printf("%s = %s", $e->getCode(), $e->getMessage());
            exit;
        }

        return $result;
    }

    function getUserByNumber($studentNumber)
    {
        try {
            $result = $this->dbObject->getUserByNumber($studentNumber);

        } catch (Exception $e) {
            printf("%s = %s", $e->getCode(), $e->getMessage());
            exit;
        }

        return $result;
    }

    function getUserByEmail($email)
    {
        try {
            $result = $this->dbObject->getUserByEmail($email);

        } catch (Exception $e) {
            printf("%s = %s", $e->getCode(), $e->getMessage());
            exit;
        }

        return $result;
    }

    function getUserByUserID($userID)
    {
        try {
            $result = $this->dbObject->getUserByID($userID);

        } catch (Exception $e) {
            printf("%s = %s", $e->getCode(), $e->getMessage());
            exit;
        }

        return $result;
    }

    function connect($dbServer, $userName, $password, $db)
    {
        // TODO: Implement connect() method.
    }
}