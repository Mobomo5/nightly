<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/15/14
 * Time: 10:56 PM
 */
class mySQL implements databaseInterface {
    private $mysqli;
    private $dbServer;
    private $dbUsername;
    private $dbPassword;
    private $db;
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
    /**
     * @bool Returns false if there are any connection errors, otherwise returns true.
     */
    function isConnected() {
        if ($this->mysqli->connect_error) {
            return false;
        }
        return true;
    }
    function connect() {
        if (empty($this->dbUsername) or empty($this->db) or empty($this->dbPassword) or empty($this->dbServer)) {
            echo "failed";
            return false;
        }
        return $this->mysqli = new mysqli($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db);
    }
    function disconnect() {
        if (!isset($this->mysqli)) {
            return false;
        }
        $this->mysqli->close();
    }
    /**
     * returns an associative array of values stored as $result[row][column]=>value
     * @param string $select
     * @param string $from
     * @param mixed|string $where
     * @throws Exception
     * @return array
     */
    public function getData($select, $from, $where = '1') {
        $select = $this->mysqli->real_escape_string($select);
        $from = $this->mysqli->real_escape_string($from);
        $where = $this->mysqli->real_escape_string($where);
        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where . ";";
        if (!$results = $this->mysqli->query($query)) {
            return false;
        }
        $numRows = $results->num_rows;
        if ($numRows == 0) {
            return false;
        }
        $resultsArray = $this->makeAssoc($results);
        return $resultsArray;
    }
    function makeCustomQuery($inQuery) {
        $query = $this->mysqli->real_escape_string($inQuery);
        if (!($results = $this->mysqli->query($query))) {
            throw new Exception("There was an error in the query: " . $this->mysqli->error);
        }
        $resultsArray = $this->makeAssoc($results);
        return $resultsArray;
    }
    function insertData($into, $columns, $values) {
        $query = 'INSERT INTO ' . $into . ' (' . $columns . ') VALUES (' . $values . ');';
        $results = $this->mysqli->query($query);
        if (!$results) {
            return false;
        }
        return true;
    }
    function updateTable($table, $set, $values) {
        $query = 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $values . ';';
        $results = $this->mysqli->query($query);
        return $results;
    }
    private function makeAssoc($results) {
        $numRows = $results->num_rows;
        $resultArray = Array($numRows);
        for ($i = 0; $i < $numRows; $i++) {
            $resultArray[$i] = $results->fetch_assoc();
        }
        return $resultArray;
    }
    function configure($dbServer, $userName, $password, $db) {
        $this->db = $db;
        $this->dbServer = $dbServer;
        $this->dbPassword = $password;
        $this->dbUsername = $userName;
    }
    function escapeString($inString) {
        $escapedString = $this->mysqli->real_escape_string($inString);
        return $escapedString;
    }
    function removeData($from, $where) {
        $query = 'DELETE FROM ' . $from . ' WHERE ' . $where . ';';
        $results = $this->mysqli->query($query);
        return $results;
    }
}