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
    public static function getRequiredPHPDatabaseModule();
    public function isConnected();
    public function connect();
    function disconnect();
    function getData($select, $from, $where);
    function makeCustomQuery($inQuery);
    function insertData($into, $columns, $values);
    function updateTable($table, $set, $where);
    function configure($dbServer, $userName, $password, $db);
    function escapeString($inString);
    function removeData($from, $where);
    function getError();
    function getLastInsertID();
}