<?php
interface IDatabase {
    public static function getInstance();
    public static function getRequiredPHPDatabaseModule();
    public function isConnected();
    public function connect();
    public function disconnect();
    public function getData($select, $from, $where);
    public function insertData($into, $columns, $values);
    public function updateTable($table, $set, $where);
    public function removeData($from, $where);
    public function makeCustomQuery($inQuery);
    public function configure($dbServer, $userName, $password, $db);
    public function escapeString($inString);
    public function getError();
    public function getLastInsertID();
}