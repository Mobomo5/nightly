<?php

require_once(DATABASE_INTERFACE_FILE);
require_once(NOTICE_ENGINE_OBJECT_FILE);
require_once(NOTICE_OBJECT_FILE);

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
        if (!self::$instance->isConnected()) {
            echo "The database broke! :(";
            exit;
        }
        return self::$instance;
    }

    private function __construct() {
        require_once(EDUCASK_ROOT . '/includes/config.php');
        $this->dbUsername = $dbUserName;
        $this->dbPassword = $dbPassword;
        $this->db = $db;
        $this->dbServer = $dbServer;
        $this->dbType = $dbType;
        // Dynamically create the new database object, if possible.
        if (!include_once(EDUCASK_ROOT . "/includes/databases/" . $this->dbType . ".php")) { //used include because I don't want a fatal error.
            new notice('error', 'There appears to be no ' . $this->dbType . ' database available. Please check the config.php file.');
            echo 'There appears to be no ' . $this->dbType . ' database available. Please check the config.php file.';
        }
        $this->dbObject = $dbType::getInstance();
        $this->dbObject->configure($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db);
        $this->dbObject->connect();
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

    /**
     * returns true when connection is available, false otherwise
     * @return bool
     */
    public function isConnected() {
        if (empty($this->dbObject)) {
            return false;
        }
        return $this->dbObject->isConnected();
    }

    public function bootstrapDisconnect() {
        $this->dbObject->disconnect();
    }

    /**
     * disconnects the database
     */
    public function disconnect() {
        $this->dbObject->disconnect();
    }

    /**
     * Calls the sub-database's getData function
     * returns false on failure, Data Array on success
     *
     * @param string $select
     * @param string $from
     * @param string $where
     * @return bool
     */
    public function getData($select, $from, $where = '1') {
        if (empty($select) OR empty($from) OR empty($where)) {
            return false;
        }
        $result = $this->dbObject->getData($select, $from, $where);
        if (!$result) {
            return false; //@todo: link to last page.
        }
        return $result;
    }

    /**
     * Allows the user to make custom queries. May be removed before release
     *
     * @param $inQuery
     * @return bool
     */
    public function makeCustomQuery($inQuery) {
        $result = $this->dbObject->query($inQuery);
        if (!$result) {
            new notice("error", "There was an error in the statement"); //@todo: better error messages
            return false; //@todo: link to last page.
        }
        return $result;
    }

    /**
     * inserts data into a table. returns true on success, false on failure
     *
     * @param $into
     * @param $columns
     * @param $values
     * @return bool
     */
    public function insertData($into, $columns, $values) {
        if (empty($into) OR empty($columns) OR empty($values)) {
            return false;
        }
        $result = $this->dbObject->insertData($into, $columns, $values);
        if (!$result) {
            new notice("error", "There was an error in the statement"); //@todo: better error messages
            return false; //@todo: link to last page.
        }
        return $result;
    }

    /**
     * updates supplied table. returns true on success, false on fail
     *
     * @param $table
     * @param $set
     * @param $where
     * @return bool
     */
    public function updateTable($table, $set, $where) {
        if (empty($table) OR empty($set) OR empty($where)) {
            return false;
        }
        $result = $this->dbObject->updateTable($table, $set, $where);
        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * calls the sub-database's connect function
     */
    public function connect() {
        $this->dbObject->connect();
    }

    /**
     * intentionally left empty
     *
     * @param $dbServer
     * @param $userName
     * @param $password
     * @param $db
     */
    function configure($dbServer, $userName, $password, $db) {
        // does nothing in the databaseCreator
    }

    /**
     * returns an escaped string
     *
     * @param $inString
     * @return bool
     */
    function escapeString($inString) {
        if (empty($inString)) {
            return false;
        }
        $escapedString = $this->dbObject->escapeString($inString);
        return $escapedString;
    }

    /**
     * delete data from the db
     *
     * @param $from
     * @param $where
     * @return bool
     */
    function removeData($from, $where) {
        if (empty($from) OR empty($where)) {
            return false;
        }
        return $this->dbObject->removeData($from, $where);
    }
}