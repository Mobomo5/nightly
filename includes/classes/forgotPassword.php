<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 9/11/14
 * Time: 11:04 AM
 */
require_once(GENERAL_ENGINE_OBJECT_FILE);
class forgotPassword {
    private $id;
    private $token;
    private $requestDate;
    private $userID;
    public function __construct($id, $token, DateTime $requestDate, $userID) {
        if(! is_numeric($id)) {
            return;
        }
        if(! is_numeric($userID)) {
            return;
        }
        preg_replace('/\s+/', '', strip_tags($token));
        $cleanString = new general('cleanString');
        if(! $cleanString->functionsExists()) {
            return;
        }
        $token = $cleanString->run(array('stringToClean' => $token));
        $this->id = $id;
        $this->token = $token;
        $this->requestDate = $requestDate;
        $this->userID = $userID;
    }
    public function getID() {
        return $this->id;
    }
    public function getToken() {
        return $this->token;
    }
    public function getRequestDate() {
        return $this->requestDate;
    }
    public function getUserID() {
        return $this->userID;
    }
    public function verify($inToken, $inUserID) {
        if(! is_numeric($inUserID)) {
            return false;
        }
        $inToken = preg_replace('/\s+/', '', strip_tags($inToken));
        $cleanString = new general('cleanString');
        if(! $cleanString->functionsExists()) {
            return false;
        }
        $inToken = $cleanString->run(array('stringToClean' => $inToken));
        if(strcasecmp($this->token, $inToken) !== 0) {
            return false;
        }
        if($this->userID !== $inUserID) {
            return false;
        }
        return true;
    }
} 