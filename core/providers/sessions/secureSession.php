<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 11/05/2015
 * Time: 8:57 PM
 */
class secureSession implements SessionHandlerInterface, ISession {
    private $key;
    public function __construct() {
        if(! isset($_COOKIE['educaskS'])) {
            $this->newCookie();
            return;
        }
        $cookieData = explode(" # ", $_COOKIE['educaskS']);
        if(! Hasher::hmacVerify($cookieData[1], $cookieData[0])) {
            $this->newCookie();
            return;
        }
        $this->key = $_COOKIE['educaskS'];
    }
    private function newCookie() {
        $randomStringGenerator = new generateRandomString(30, true, 50, 300);
        $randomString = $randomStringGenerator->run();
        $this->key = Hasher::generateHmacHash($randomString) . " # " . $randomString;
        $aSingleDay = 24 * 60 * 60;
        $cookieExpireTime = time() + $aSingleDay;
        setcookie("educaskS", $this->key, $cookieExpireTime, null, null, null, true);
    }
    public function open($savePath, $sessionName) {
        return true;
    }
    public function close() {
        return true;
    }
    public function read($id) {
        if(! is_string($id)) {
            return "";
        }
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return "";
        }
        $id = $database->escapeString($id);
        $this->waitForUnlock($id);
        $rawData = $database->getData("variables", "session", "BINARY id='{$id}'");
        if(! $rawData) {
            return "";
        }
        if(count($rawData) > 1) {
            return "";
        }
        $encrypter = new Encrypter($this->key);
        return $encrypter->decrypt($rawData[0]['variables']);
    }
    public function write($sessionID, $data) {
        if(! is_string($sessionID)) {
            return false;
        }
        if(! is_string($data)) {
            return false;
        }
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $encrypter = new Encrypter($this->key);
        $data = $database->escapeString($encrypter->encrypt($data));
        $sessionID = $database->escapeString($sessionID);
        $time = $database->escapeString(time());
        $query = "INSERT INTO session (id, variables, lastAccess, locked) VALUES ('{$sessionID}', '{$data}', '{$time}', 0) ON DUPLICATE KEY UPDATE variables='{$data}', lastAccess='{$time}', locked=0";
        $result = $database->makeCustomQuery($query);
        if($result == false) {
            return false;
        }
        return true;
    }
    public function destroy($id) {
        if(! is_string($id)) {
            return false;
        }
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($id);
        $result = $database->removeData("session", "BINARY id='{$id}'");
        if(! $result) {
            return false;
        }
        return true;
    }
    public function gc($maxLifeTime) {
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $cutOff = time() - $maxLifeTime;
        $cutOff = $database->escapeString($cutOff);
        $result = $database->removeData("session", "lastAccess < '{$cutOff}' AND locked=0");
        if(! $result) {
            return false;
        }
        return true;
    }
    private function waitForUnlock($hashedID) {
        if(! is_string($hashedID)) {
            return;
        }
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return;
        }
        $hashedID = $database->escapeString($hashedID);
        while($database->getData("locked", "session", "BINARY id='{$hashedID}' AND locked=1") !== null) {
            usleep(5);
        }
        $database->updateTable("session", "locked=1", "BINARY id='{$hashedID}' AND locked=0");
    }
}