<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/11/14
 * Time: 4:25 PM
 */
require_once(VALIDATOR_OBJECT_FILE);
require_once(DATABASE_OBJECT_FILE);
require_once(VARIABLE_ENGINE_OBJECT_FILE);
require_once(VARIABLE_OBJECT_FILE);
require_once(LOCKOUT_OBJECT_FILE);
class lockoutEngine {
    private static $instance;
    private $foundLockouts;
    public static function getInstance() {
        if(! isset(self::$instance)) {
            self::$instance = new lockoutEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundLockouts = array();
    }
    public function getLockout($ipAddress) {
        $val = new validator('ip');
        if(! $val->validatorExists()) {
            return false;
        }
        if(! $val->validate($ipAddress)) {
            return false;
        }
        if(isset($this->foundLockouts[$ipAddress])) {
            return $this->foundLockouts[$ipAddress];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $ipAddress = $database->escapeString($ipAddress);
        $results = $database->getData('numberOfFailedAttempts, lastUpdate, attemptsLeft', 'lockout', "ipAddress='{$ipAddress}'");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $lastUpdate = new DateTime($results[0]['lastUpdate']);
        $lockout = new lockout($ipAddress, $results[0]['numberOfFailedAttempts'], $lastUpdate, $results[0]['attemptsLeft']);
        $this->foundLockouts[$ipAddress] = $lockout;
        return $lockout;
    }
    public function addLockout(lockout $lockoutToAdd) {
        $ip = $lockoutToAdd->getIP();
        $failedAttempts = $lockoutToAdd->getNumberOfFailedAttempts();
        $attemptsLeft = $lockoutToAdd->getNumberOfAttemptsLeft();
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $ip = $database->escapeString($ip);
        if(! is_int($failedAttempts)) {
            $failedAttempts = $database->escapeString($failedAttempts);
        }
        if(! is_int($attemptsLeft)) {
            $attemptsLeft = $database->escapeString($attemptsLeft);
        }
        $success = $database->insertData('lockout', 'ipAddress, numberOfFailedAttempts, attemptsLeft', "'{$ip}', {$failedAttempts}, {$attemptsLeft}");
        if($success == false) {
            return false;
        }
        return true;
    }
    public function setLockout(lockout $lockoutToSet) {
        $ip = $lockoutToSet->getIP();
        $failedAttempts = $lockoutToSet->getNumberOfFailedAttempts();
        $attemptsLeft = $lockoutToSet->getNumberOfAttemptsLeft();
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $ip = $database->escapeString($ip);
        if(! is_int($failedAttempts)) {
            $failedAttempts = $database->escapeString($failedAttempts);
        }
        if(! is_int($attemptsLeft)) {
            $attemptsLeft = $database->escapeString($attemptsLeft);
        }
        $success = $database->updateTable('lockout', "numberOfFailedAttempts={$failedAttempts}, attemptsLeft={$attemptsLeft}", "ipAddress='{$ip}'");
        if($success == false) {
            return false;
        }
        return true;
    }
    public function removeLockout(lockout $lockoutToRemove) {
        $ip = $lockoutToRemove->getIP();
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $ip = $database->escapeString($ip);
        $success = $database->removeData('lockout', "ipAddress='{$ip}'");
        if($success == false) {
            return false;
        }
        return true;
    }
    public function isLockedOut($ipAddress) {
        $val = new validator('ip');
        if(! $val->validatorExists()) {
            return false;
        }
        //Lock the client out if the clients IP isn't valid
        if(! $val->validate($ipAddress)) {
            return true;
        }
        $lockout = $this->getLockout($ipAddress);
        if($lockout == false) {
            return false;
        }
        if($lockout->getNumberOfAttemptsLeft() > 0) {
            return false;
        }
        $lockoutPeriod = $this->getLockoutPeriod();
        $totalLockoutLength = $lockout->getNumberOfFailedAttempts() * $lockoutPeriod;
        $lastUpdate = clone $lockout->lastUpdated();
        $lockedOutUntil = $lastUpdate->add(DateInterval::createFromDateString($totalLockoutLength . ' minutes'));
        $currentTime = new DateTime();
        if($currentTime >= $lockedOutUntil) {
            $lockout->reEnable($this->getNumberOfAttemptsBeforeLockout());
            $this->setLockout($lockout);
            return false;
        }
        return true;
    }
    public function getLockoutPeriod() {
        $variableEngine = variableEngine::getInstance();
        $lockoutPeriod = $variableEngine->getVariable('lockoutPeriod');
        if($lockoutPeriod == null) {
            return 10;
        }
        return intval($lockoutPeriod->getValue());
    }
    public function setLockoutPeriod($inTime) {
        if(! is_int($inTime)) {
            return false;
        }
        $variableEngine = variableEngine::getInstance();
        $lockoutPeriod = $variableEngine->getVariable('lockoutPeriod');
        $lockoutPeriod->setValue($inTime);
        $success = $lockoutPeriod->save();
        if($success == false) {
            return false;
        }
        return true;
    }
    public function getNumberOfAttemptsBeforeLockout() {
        $variableEngine = variableEngine::getInstance();
        $numberOfAttemptsBeforeLockout = $variableEngine->getVariable('numberOfAttemptsBeforeLockout');
        if($numberOfAttemptsBeforeLockout == null) {
            return 3;
        }
        return intval($numberOfAttemptsBeforeLockout->getValue());
    }
    public function setNumberOfAttemptsBeforeLockout($inNumberOfAttempts) {
        if(! is_int($inNumberOfAttempts)) {
            return false;
        }
        $variableEngine = variableEngine::getInstance();
        $numberOfAttemptsBeforeLockout = $variableEngine->getVariable('numberOfAttemptsBeforeLockout');
        $numberOfAttemptsBeforeLockout->setValue($inNumberOfAttempts);
        $success = $numberOfAttemptsBeforeLockout->save();
        if($success == false) {
            return false;
        }
        return true;
    }
} 