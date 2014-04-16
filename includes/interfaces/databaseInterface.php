<?php

/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/15/14
 * Time: 1:14 PM
 */
interface databaseInterface
{
    function isConnected();

    function connect($dbServer, $userName, $password, $db);

    function disconnect();

    function select($select, $from, $where);

    function query($inQuery);

    function insert($into, $columns, $values);

    function update($table, $set, $values);

    function getUserByName($firstName, $lastName);

    function getUserByNumber($studentNumber);

    function getUserByEmail($email);

    function getUserByUserID($userID);

} 