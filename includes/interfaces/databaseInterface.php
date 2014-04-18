<?php

/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/15/14
 * Time: 1:14 PM
 *
 * Used when a user wants to create a new type of database connection that isn't mySQL. You can implement this interface and the database.php functions will interact properly.
 */
interface databaseInterface {
    public static function getInstance();

    public function isConnected();

    public function connect($dbServer, $userName, $password, $db);

    function disconnect();

    function select($select, $from, $where);

    function makeCustomQuery($inQuery);

    function insert($into, $columns, $values);

    function update($table, $set, $values);

    function getUserByName($firstName, $lastName);

    function getUserByNumber($studentNumber);

    function getUserByEmail($email);

    function getUserByUserID($userID);

} 