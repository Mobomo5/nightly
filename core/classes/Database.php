<?php
class Database implements IDatabase {
    private static $instance;
    private $dbUsername;
    private $dbPassword;
    private $db;
    private $dbServer;
    private $dbObject;
    private $dbType;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    public static function getRequiredPHPDatabaseModule() {
        return '';
    }
    private function __construct() {
        $config = Config::getInstance();
        $this->dbUsername = $config->getDatabaseUsername();
        $this->dbPassword = $config->getDatabasePassword();
        $this->db = $config->getDatabaseName();
        $this->dbServer = $config->getDatabaseServer();
        $dbType = str_replace('..', '', $config->getDatabaseType());
        $this->dbType = $dbType;
        // Dynamically create the new database object, if possible.
        if(!include_once(EDUCASK_ROOT . "/core/providers/databases/" . $this->dbType . ".php")) { //used include because I don't want a fatal error.
            echo 'There appears to be no ' . $this->dbType . ' database available. Please check the config.php file.';
        }
        $this->dbObject = $dbType::getInstance();
        $this->dbObject->configure($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db);
        $this->dbObject->connect();
    }
    public function isConnected() {
        if (empty($this->dbObject)) {
            return false;
        }
        return $this->dbObject->isConnected();
    }
    public function connect() {
        $this->dbObject->connect();
    }
    public function bootstrapDisconnect() {
        $this->dbObject->disconnect();
    }
    public function disconnect() {
        if (!$this->isConnected()) {
            return;
        }
        $this->dbObject->disconnect();
    }
    public function getData($select, $from, $where = '1') {
        if (empty($select) OR empty($from) OR empty($where)) {
            return false;
        }
        $result = $this->dbObject->getData($select, $from, $where);
        return $result;
    }
    public function insertData($into, $columns, $values) {
        if (empty($into) OR empty($columns) OR empty($values)) {
            return false;
        }
        $result = $this->dbObject->insertData($into, $columns, $values);
        if (!$result) {
            return false;
        }
        return true;
    }
    public function updateTable($table, $set, $where) {
        if (empty($table) OR empty($set) OR empty($where)) {
            return false;
        }
        $result = $this->dbObject->updateTable($table, $set, $where);
        if (!$result) {
            return false;
        }
        return true;
    }
    public function removeData($from, $where) {
        if (empty($from) OR empty($where)) {
            return false;
        }
        return $this->dbObject->removeData($from, $where);
    }
    public function makeCustomQuery($inQuery) {
        $result = $this->dbObject->makeCustomQuery($inQuery);
        if (!$result) {
            return false;
        }
        return $result;
    }
    public function configure($dbServer, $userName, $password, $db) {
        // does nothing in the databaseCreator
    }
    public function escapeString($inString) {
        if (empty($inString)) {
            return false;
        }
        $escapedString = $this->dbObject->escapeString($inString);
        return $escapedString;
    }
    public function getError() {
        return $this->dbObject->getError();
    }
    public function getLastInsertID() {
        return $this->dbObject->getLastInsertID();
    }
    private function __clone() {
        //Me not like clones! Me smash clones!
    }
    public function __destruct() {
        $this->disconnect();
    }
}