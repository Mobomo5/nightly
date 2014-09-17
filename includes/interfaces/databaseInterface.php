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
    public function disconnect();
    public function getData($select, $from, $where);
    public function makeCustomQuery($inQuery);
    public function insertData($into, $columns, $values);
    public function updateTable($table, $set, $where);
    public function configure($dbServer, $userName, $password, $db);
    public function escapeString($inString);
    public function removeData($from, $where);
    public function getError();
    public function getLastInsertID();
}