<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 15/06/14
 * Time: 7:25 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(URL_ALIAS_OBJECT_FILE);
class urlAliasEngine {
    private static $instance;
    public static function getInstance() {
        if(! isset(self::$instance)) {
            self::$instance = new urlAliasEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing.
    }
    public function getAliasFromID($id) {
        if(! is_numeric($id)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($id);
        $rawData = $database->getData('*', 'urlAlias', "aliasID={$id}");
        if($rawData === false) {
            return false;
        }
        if($rawData === null) {
            return false;
        }
        if(count($rawData) > 1) {
            return false;
        }
        return new urlAlias($rawData[0]['aliasID'], $rawData[0]['source'], $rawData[0]['alias']);
    }
    public function getAliasFromAlias($alias) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $alias = $database->escapeString(preg_replace('/\s+/', '', $alias));
        $rawData = $database->getData('*', 'urlAlias', "alias='{$alias}'");
        if($rawData === false) {
            return false;
        }
        if($rawData === null) {
            return false;
        }
        if(count($rawData) > 1) {
            return false;
        }
        return new urlAlias($rawData[0]['aliasID'], $rawData[0]['source'], $rawData[0]['alias']);
    }
    public function addAlias(urlAlias $toAdd) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canAddUrlAliases')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $source = $database->escapeString($toAdd->getSource());
        $alias = $database->escapeString($toAdd->getAlias());
        $added = $database->insertData('urlAlias', 'source, alias', "'{$source}', '{$alias}'");
        if($added === false) {
            return false;
        }
        return true;
    }
    public function editAlias(urlAlias $toEdit) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canEditUrlAliases')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toEdit->getID());
        if(! is_numeric($id)) {
            return false;
        }
        $source = $database->escapeString($toEdit->getSource());
        $alias = $database->escapeString($toEdit->getAlias());
        $edited = $database->updateTable('urlAlias', "source='{$source}', alias='{$alias}'", "aliasID={$id}");
        if($edited === false) {
            return false;
        }
        return true;
    }
    public function deleteAlias(urlAlias $toDelete) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canDeleteUrlAliases')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toDelete->getID());
        if(! is_numeric($id)) {
            return false;
        }
        $deleted = $database->removeData('urlAlias', "aliasID={$id}");
        if($deleted === false) {
            return false;
        }
        return true;
    }
} 