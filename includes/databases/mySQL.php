<?php

/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/15/14
 * Time: 10:56 PM
 */
class mySQL implements databaseInterface
{

    private $mysqli;
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new mySQL();
        }
        return self::$instance;
    }

    function isConnected()
    {
        if (!$this->mysqli->get_connection_stats()) {
            return false;
        }

        return true;
    }

    function connect($dbServer, $username, $password, $db)
    {
        return $this->mysqli = new mysqli($dbServer, $username, $password, $db);
    }

    function disconnect()
    {
        if (!isset($this->mysqli)) {
            throw new Exception("No database object connected.");
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
    public function select($select, $from, $where = '1')
    {

        $select = $this->mysqli->real_escape_string($select);
        $from = $this->mysqli->real_escape_string($from);
        $where = $this->mysqli->real_escape_string($where);

        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where . ";";

        if (!$results = $this->mysqli->query($query)) {
            throw new Exception($this->mysqli->error);
        }
        $numRows = $results->num_rows;

        if ($numRows == 0) {
            throw new Exception("Returned 0 rows.");
        }

        $resultsArray = $this->makeAssoc($results);

        return $resultsArray;
    }


    function query($inQuery)
    {
        $query = $this->mysqli->real_escape_string($inQuery);

        $results = $this->mysqli->query($query);

        $resultsArray = $this->makeAssoc($results);

        return $resultsArray;

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
        $firstName = $this->mysqli->real_escape_string($firstName);
        $lastName = $this->mysqli->real_escape_string($lastName);

        $query = "SELECT * FROM users WHERE upper(firstName) = upper('" . $firstName . "') AND upper(lastName) = upper('" . $lastName . "');";
        echo $query;
        if (!($results = $this->mysqli->query($query))) {
            throw new Exception($this->mysqli->error);
        }

        if ($results->num_rows < 1) {
            throw new Exception("Returned 0 rows.");
        }

        if ($results->num_rows > 1) {
            throw new Exception("Returned too many rows.");
        }

        $assoc = $results->fetch_assoc();

        $results->free();

        return $assoc;
    }

    /**
     * @param $studentNumber
     * returns all columns relating to a user except password as an associative array using prepared statements
     */
    function getUserByNumber($studentNumber)
    {

        $studentNumber = $this->mysqli->real_escape_string($studentNumber);

        $query = "SELECT * FROM users WHERE studentNumber = '" . $studentNumber . "';"; //@todo: Ensure this doesn't return the password

        if (!($results = $this->mysqli->query($query))) {
            throw new Exception($this->mysqli->error);
        }

        if ($results->num_rows < 1) {
            throw new Exception("Returned 0 rows.");
        }

        if ($results->num_rows > 1) {
            throw new Exception("Returned too many rows.");
        }

        $assoc = $results->fetch_assoc();

        $results->free();

        return $assoc;

//        $stmt = $this->mysqli->stmt_init();
//        echo "stmt = " . ($stmt->prepare("SELECT * FROM users WHERE studentNumber = ?"));
//
//
//
//        $userName = "";
//        $firstName = "";
//        $lastName = "";
//        $accountCreationDate = "";
//        $email = "";
//        $studentNumber = "";
//        $password = "";
//        $grade = "";
//        $userID = "";
//        $roleID = "";
//        $birthday = "";
//        $lastAccess = "";
//
//        echo $stmt->bind_param("s", $studentNumber);
//        echo $stmt->execute();
//        echo $stmt->bind_result($userName, $firstName, $lastName, $accountCreationDate, $email, $studentNumber, $password, $grade, $userID, $roleID, $birthday, $lastAccess);
//
//
//        echo $stmt->fetch();
//        echo "<br>error : ";
//        echo "<br>affected rows = " . $stmt->affected_rows;
//        var_dump($userName);
//        $stmt->close();
    }

    function getUserByEmail($email)
    {
        $email = $this->mysqli->real_escape_string($email);

        $query = "SELECT * FROM users WHERE upper(email) = upper('" . $email . "');"; //@todo: Ensure this doesn't return the password

        if (!($results = $this->mysqli->query($query))) {
            throw new Exception($this->mysqli->error);
        }

        if ($results->num_rows < 1) {
            throw new Exception("Returned 0 rows.");
        }

        if ($results->num_rows > 1) {
            throw new Exception("Returned too many rows.");
        }

        $assoc = $results->fetch_assoc();

        $results->free();

        return $assoc;
    }

    function getUserByUserID($userID)
    {
        $userID = $this->mysqli->real_escape_string($userID);

        $query = "SELECT * FROM users WHERE userID = '" . $userID . "';"; //@todo: Ensure this doesn't return the password

        if (!($results = $this->mysqli->query($query))) {
            throw new Exception($this->mysqli->error);
        }

        if ($results->num_rows < 1) {
            throw new Exception("Returned 0 rows.");
        }

        if ($results->num_rows > 1) {
            throw new Exception("Returned too many rows.");
        }

        $assoc = $results->fetch_assoc();

        $results->free();

        return $assoc;
    }

    private function makeAssoc($results) {

        $numRows = $results->num_rows;

        $resultArray = Array($numRows);

        for ($i = 0; $i < $numRows; $i++) {
            $resultArray[$i] = $results->fetch_assoc();
        }

        return $resultArray;
    }


}