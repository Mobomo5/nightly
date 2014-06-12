<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/11/14
 * Time: 4:24 PM
 */
require_once(VALIDATOR_OBJECT_FILE);
class lockout {
    private $ipAddress;
    private $numberOfFailedAttempts;
    private $lastUpdate;
    private $attemptsLeft;
    public function __construct($ipAddress, $numberOfFailedAttempts, DateTime $lastUpdate, $numberOfAttemptsLeft) {
        if(! is_int($numberOfFailedAttempts)) {
            return;
        }
        if(! is_int($numberOfAttemptsLeft)) {
            return;
        }
        if(! $this->validateIP($ipAddress)) {
            return;
        }
        $this->ipAddress = $ipAddress;
        $this->numberOfFailedAttempts = $numberOfFailedAttempts;
        $this->lastUpdate = $lastUpdate;
        $this->attemptsLeft = $numberOfAttemptsLeft;
    }
    public function getIP() {
        return $this->ipAddress;
    }
    public function getNumberOfFailedAttempts() {
        return $this->numberOfFailedAttempts;
    }
    public function getNumberOfAttemptsLeft() {
        return $this->attemptsLeft;
    }
    public function lastUpdated() {
        return $this->lastUpdate;
    }
    public function failedAttempt() {
        $this->numberOfFailedAttempts += 1;
        $this->attemptsLeft -= 1;
        $this->lastUpdate = new DateTime();
    }
    private function validateIP($ip) {
        $val = new validator('ip');
        if(! $val->validatorExists()) {
            return false;
        }
        if(! $val->validate($ip)) {
            return false;
        }
        return true;
    }
} 