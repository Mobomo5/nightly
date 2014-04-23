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

        }

        return self::$instance;
    }


    // the next two are just to block the ability to do the following.
    private function __construct() {

        require_once(EDUCASK_ROOT . '/includes/config.php');
        $this->dbUsername = $dbUserName;
        $this->dbPassword = $dbPassword;
        $this->db = 'educaskOld';
        $this->dbServer = $dbServer;
        $this->dbType = $dbType;
        $this->dbType = $dbType;

        // Dynamically create the new database object, if possible.
        if (!include_once(EDUCASK_ROOT . "/includes/databases/" . $this->dbType . ".php")) { //used include because I don't want a fatal error.
            new notice('error', 'There appears to be no ' . $this->dbType . ' database available. Please check the config.php file.');
            echo 'There appears to be no ' . $this->dbType . ' database available. Please check the config.php file.';
        }
        $this->dbObject = $dbType::getInstance();
        $this->dbObject->configure($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db);
    }

    private function __clone() {
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

    public function disconnect() {
        $this->dbObject->disconnect();
    }

    public function getData($select, $from, $where = 1)
    {


        $result = $this->dbObject->select($select, $from, $where);
        if (!$result) {
            new notice("error", "There was an error in the statement"); //@todo: better error messages
            return false; //@todo: link to last page.
        }

        return $result;
    }

    public function makeCustomQuery($inQuery) {

        $result = $this->dbObject->query($inQuery);
        if (!$result) {
            new notice("error", "There was an error in the statement"); //@todo: better error messages
            return false; //@todo: link to last page.
        }

        return $result;
    }

    public function insert($into, $columns, $values) {

        $result = $this->dbObject->insert($into, $columns, $values);
        if (!$result) {
            new notice("error", "There was an error in the statement"); //@todo: better error messages
            return false; //@todo: link to last page.
        }

        return $result;
    }

    public function update($table, $set, $values) {

        $result = $this->dbObject->update($table, $set, $values);
        if (!$result) {
            new notice("error", "There was an error in the statement"); //@todo: better error messages
            return false; //@todo: link to last page.
        }
        return $result;
    }

    public function connect() {
        $this->dbObject->connect();
    }

    function configure($dbServer, $userName, $password, $db) {
        // does nothing in the databaseCreator
    }

    function escape($inString)
    {
        $escapedString = $this->dbObject->escape($inString);
        return $escapedString;
    }
}