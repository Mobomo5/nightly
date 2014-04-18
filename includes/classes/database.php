<?php

require_once(DATABASE_INTERFACE_FILE);


/**
 * Created by JetBrains PhpStorm.
 * User: Craig
 * Date: 4/10/14
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */
class database implements databaseInterface {

    /**
     * @var
     */
    private static $instance;
    private $dbUsername;
    private $dbPassword;
    private $db;
    private $dbServer;
    private $dbObject;
    private $dbType;

    /**
     * will call the new constructor methods.
     * @return database
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new database();
            self::$instance->construct();
        }

        return self::$instance;
    }


    // the next two are just to block the ability to do the following.
    protected function __construct() {
        //Thou shalt not construct that which is unconstructable!
    }

    protected function __clone() {
        //Me not like clones! Me smash clones!
    }


    /**
     * Will be called when the code no longer needs the class.
     */
    public function __destruct() {
        $this->disconnect();
    }


    public function isConnected() {
        if (empty($this->dbObject)) {
            return false;
        }
        return $this->dbObject->isConnected();
    }

    public function construct() {

        require_once(EDUCASK_ROOT . '/includes/config.php');
        $this->dbUsername = $dbUserName;
        $this->dbPassword = $dbPassword;
        $this->db = 'educaskOld';
        $this->dbServer = $dbServer;
        $this->dbType = $dbType;

        // Dynamically create the new database object, if possible.
        if (!include_once(EDUCASK_ROOT . "/includes/databases/" . $this->dbType . ".php")) { //used include because I don't want a fatal error.
            new notice('error', 'There appears to be no ' . $this->dbType . ' database available. Please check the config.php file.');
            echo 'There appears to be no ' . $this->dbType . ' database available. Please check the config.php file.';
        }

        $this->dbObject = $dbType::getInstance();
        try {
            $this->dbObject->connect($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db);
        } catch (Exception $e) {
            new notice('error', $e->getMessage());
        }
    }

    public function disconnect() {
        $this->dbObject->disconnect();
    }

    public function select($select, $from, $where = 1) {

        try {
            $result = $this->dbObject->select($select, $from, $where);
        } catch (Exception $e) {
            new notice("error", $e->getMessage());
            echo $e->getMessage();
        }
        return $result;
    }

    public function makeCustomQuery($inQuery) {

        try {
            $result = $this->dbObject->query($inQuery);
        } catch (Exception $e) {
            new notice('error', $e->getMessage());
        }

        return $result;
    }

    public function insert($into, $columns, $values) {
        try {
            $result = $this->dbObject->insert($into, $columns, $values);
        } catch (Exception $e) {
            new notice('error', $e->getMessage());
        }

        return $result;
    }

    public function update($table, $set, $values) {
        try {
            $result = $this->dbObject->update($table, $set, $values);
        } catch (Exception $e) {
            new notice('error', $e->getMessage());
        }
        return $result;
    }

    public function getUserByName($firstName, $lastName) {
        try {
            $result = $this->dbObject->getUserByName($firstName, $lastName);
        } catch (Exception $e) {
            new notice("error", $e->getMessage());
        }

        return $result;
    }

    public function getUserByNumber($studentNumber) {
        try {
            $result = $this->dbObject->getUserByNumber($studentNumber);
        } catch (Exception $e) {
            new notice("error", $e->getMessage());
        }

        return $result;
    }

    public function getUserByEmail($email) {
        try {
            $result = $this->dbObject->getUserByEmail($email);
        } catch (Exception $e) {
            new notice('error', $e->getMessage());
        }

        return $result;
    }

    public function getUserByUserID($userID) {

        try {
            $result = $this->dbObject->getUserByID($userID);
        } catch (Exception $e) {
            new notice('error', $e->getMessage());
        }

        return $result;
    }

    public function connect($dbServer, $userName, $password, $db) {
        // TODO: Implement connect() method.
    }
}