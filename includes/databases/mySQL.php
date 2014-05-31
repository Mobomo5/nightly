<?php

/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/15/14
 * Time: 10:56 PM
 */
class mySQL implements databaseInterface {
    private $mysqli = '';
    private $dbServer = '';
    private $dbUsername = '';
    private $dbPassword = '';
    private $db = '';
    private static $instance;

    /**
     * creates a new singleton mySQL database instance
     * @return mySQL
     *
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new mySQL();
        }
        return self::$instance;
    }

    private function __construct() {
        //Do nothing;
    }

    /**
     * @bool Returns false if there are any connection errors, otherwise returns true.
     */
    function isConnected() {
        if (empty($this->mysqli)) {
            return false;
        }
        if ($this->mysqli == '') {
            return false;
        }
        if (!is_object($this->mysqli)) {
            return false;
        }
        if ($this->mysqli->connect_error) {
            return false;
        }
        if (!$this->mysqli->ping()) {
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
        if ($this->dbUsername == '' or $this->db == '' or $this->dbPassword == '' or $this->dbServer == '') {
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

    /**
     * returns an associative array of values stored as $result[row][column]=>value
     *
     * @param string $select
     * @param string $from
     * @param mixed|string $where
     *
     * @throws Exception
     * @return array
     */
    public function getData($select, $from, $where = '1') {
        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where . ";";
        $results = $this->mysqli->query($query);
        if ($this->mysqli->errno) {
            return false;
        }
        $numRows = $results->num_rows;
        if ($numRows == 0) {
            return null;
        }
        $resultsArray = $this->makeAssoc($results);
        return $resultsArray;
    }

    public function makeCustomQuery($inQuery) {
        if (!($results = $this->mysqli->query($inQuery))) {
            return false;
        }
        $resultsArray = $this->makeAssoc($results);
        return $resultsArray;
    }

    public function insertData($into, $columns, $values) {
        $query = 'INSERT INTO ' . $into . ' (' . $columns . ') VALUES (' . $values . ');';
        $results = $this->mysqli->query($query);
        if (!$results) {
            return false;
        }
        return true;
    }

    public function updateTable($table, $set, $values) {
        $query = 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $values . ';';
        $results = $this->mysqli->query($query);
        return $results;
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

    public function removeData($from, $where) {
        $query = 'DELETE FROM ' . $from . ' WHERE ' . $where . ';';
        $results = $this->mysqli->query($query);
        return $results;
    }

    public function __wakeup() {
        $this->connect();
    }

    function getError() {
        return $this->mysqli->error;
    }
    function getLastInsertID() {
        return $this->mysqli->insert_id;
    }
}