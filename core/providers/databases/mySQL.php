<?php
class mySQL implements IDatabase {
    private $mysqli = '';
    private $dbServer = '';
    private $dbUsername = '';
    private $dbPassword = '';
    private $db = '';
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new mySQL();
        }
        return self::$instance;
    }
    public static function getRequiredPHPDatabaseModule() {
        return 'mysqli';
    }
    private function __construct() {
        //Do nothing;
    }
    public function isConnected() {
        if (empty($this->mysqli)) {
            return false;
        }
        if ($this->mysqli === '') {
            return false;
        }
        if (!is_object($this->mysqli)) {
            return false;
        }
        if ($this->mysqli->connect_error) {
            return false;
        }
        return true;
    }
    public function connect() {
        if ($this->isConnected()) {
            return;
        }
        if (empty($this->dbUsername) or empty($this->db) or empty($this->dbPassword) or empty($this->dbServer)) {
            return false;
        }
        if ($this->dbUsername === '' or $this->db === '' or $this->dbPassword === '' or $this->dbServer === '') {
            return false;
        }
        $this->mysqli = $mysql = new mysqli($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db);
        if ($this->mysqli->connect_errno) {
            $this->mysqli = '';
            return false;
        }
        self::$instance = $this;
    }
    public function disconnect() {
        if (!$this->isConnected()) {
            return;
        }
        $this->mysqli->close();
        unset($this->mysqli);
        self::$instance = $this;
    }
    public function getData($select, $from, $where = '1') {
        $query = "SELECT {$select} FROM {$from} WHERE {$where};";
        $results = $this->mysqli->query($query);
        if ($this->mysqli->errno) {
            return false;
        }
        $numRows = $results->num_rows;
        if ($numRows === 0) {
            return null;
        }
        $resultsArray = $this->makeAssoc($results);
        return $resultsArray;
    }
    public function insertData($into, $columns, $values) {
        $query = "INSERT INTO {$into} ({$columns}) VALUES ({$values});";
        $results = $this->mysqli->query($query);
        if (!$results) {
            return false;
        }
        return true;
    }
    public function updateTable($table, $set, $where) {
        $query = "UPDATE {$table} SET {$set} WHERE {$where};";
        $results = $this->mysqli->query($query);
        return $results;
    }
    public function removeData($from, $where) {
        $query = "DELETE FROM {$from} WHERE {$where};";
        $results = $this->mysqli->query($query);
        if(! $results) {
            return false;
        }
        return true;
    }
    public function makeCustomQuery($inQuery) {
        if (!($results = $this->mysqli->query($inQuery))) {
            return false;
        }
        if (!is_object($results)) {
            return $results;
        }
        $resultsArray = $this->makeAssoc($results);
        return $resultsArray;
    }
    private function makeAssoc($results) {
        $numRows = $results->num_rows;
        $resultArray = array();
        for ($i = 0; $i < $numRows; $i++) {
            $resultArray[$i] = $results->fetch_assoc();
        }
        return $resultArray;
    }
    public function configure($dbServer, $userName, $password, $db) {
        $this->db = $db;
        $this->dbServer = $dbServer;
        $this->dbPassword = $password;
        $this->dbUsername = $userName;
        self::$instance = $this;
    }
    public function escapeString($inString) {
        $escapedString = $this->mysqli->real_escape_string($inString);
        return $escapedString;
    }
    public function getError() {
        return $this->mysqli->error;
    }
    public function getLastInsertID() {
        return $this->mysqli->insert_id;
    }
    public function __wakeup() {
        $this->connect();
    }
}