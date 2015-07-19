<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/05/2015
 * Time: 8:53 PM
 */
class ObjectCache {
    private static $instance;
    private $objects;
    private $lastClear;
    public static function getInstance() {
        if(isset(self::$instance)) {
            return self::$instance;
        }
        $file = EDUCASK_ROOT . "/cache/educask.cache";
        if(! is_readable($file)) {
            self::$instance = new ObjectCache();
            return self::$instance;
        }
        self::$instance = unserialize(file_get_contents($file));
        return self::$instance;
    }
    public static function saveInstance() {
        if(!isset(self::$instance)) {
            return;
        }
        $file = EDUCASK_ROOT . "/cache/educask.cache";
        if(is_readable($file)) {
            self::clearOldCache();
        }
        $content = serialize(self::$instance);
        file_put_contents($file, $content, LOCK_EX);
    }
    private static function clearOldCache() {
        if(self::$instance->lastClear > strtotime("-30 minutes")) {
            return;
        }
        self::$instance = new ObjectCache();
    }
    private function __construct(){
        $this->objects = array();
        $this->lastClear = time();
    }
    public function getObject($inObjectName) {
        if(! is_string($inObjectName)) {
            return false;
        }
        if(! isset($this->objects[$inObjectName])) {
            return false;
        }
        return $this->objects[$inObjectName];
    }
    public function setObject($inObjectName, $inObject, $overwrite = false) {
        if(! is_string($inObjectName)) {
            return false;
        }
        if(isset($this->objects[$inObjectName]) && ($overwrite === false)) {
            return false;
        }
        $this->objects[$inObjectName] = $inObject;
    }
    public function getEncryptedObject($inObjectName) {
        $object = $this->getObject($inObjectName);
        if($object === false) {
            return false;
        }
        if(! is_string($object)) {
            return false;
        }
        $encrypter = new Encrypter();
        return unserialize($encrypter->decrypt($object));
    }
    public function setEncryptedObject($inObjectName, $inObject, $overwrite = false) {
        $encrypter = new Encrypter();
        $inObject = $encrypter->encrypt(serialize($inObject));
        return $this->setObject($inObjectName, $inObject, $overwrite);
    }
}