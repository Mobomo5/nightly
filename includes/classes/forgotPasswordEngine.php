<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 9/11/14
 * Time: 11:04 AM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(FORGOT_PASSWORD_OBJECT_FILE);
require_once(GENERAL_ENGINE_OBJECT_FILE);
class forgotPasswordEngine {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new forgotPasswordEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing
    }
    public function getForgotPasswordByID($id) {
        if(! is_numeric($id)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($id);
        $rawData = $database->getData('*', 'forgotPassword', "requestID={$id}");
        if($rawData == false) {
            return false;
        }
        if($rawData == null) {
            return false;
        }
        if(count($rawData) > 1) {
            return false;
        }
        $date = new DateTime($rawData[0]['requestDate']);
        return new forgotPassword($rawData[0]['requestID'], $rawData[0]['token'], $date, $rawData[0]['userID']);
    }
    public function getForgotPasswordByToken($token) {
        $token = preg_replace('/\s+/', '', strip_tags($token));
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $cleanString = new general('cleanString');
        if(! $cleanString->functionsExists()) {
            return false;
        }
        $token = $cleanString->run(array('stringToClean' => $token));
        $id = $database->escapeString($token);
        $rawData = $database->getData('*', 'forgotPassword', "token='{$id}''");
        if($rawData == false) {
            return false;
        }
        if($rawData == null) {
            return false;
        }
        if(count($rawData) > 1) {
            return false;
        }
        $date = new DateTime($rawData[0]['requestDate']);
        return new forgotPassword($rawData[0]['requestID'], $rawData[0]['token'], $date, $rawData[0]['userID']);
    }
    public function getForgotPasswordByUserID($userID) {
        if(! is_numeric($userID)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $userID = $database->escapeString($userID);
        $rawData = $database->getData('*', 'forgotPassword', "userID={$userID}");
        if($rawData == false) {
            return false;
        }
        if($rawData == null) {
            return false;
        }
        if(count($rawData) > 1) {
            return false;
        }
        $date = new DateTime($rawData[0]['requestDate']);
        return new forgotPassword($rawData[0]['requestID'], $rawData[0]['token'], $date, $rawData[0]['userID']);
    }
    public function generateNewForgotPassword($userID) {
        if(! is_numeric($userID)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $randomString = new general('generateRandomString');
        if(! $randomString->functionsExists()) {
            return false;
        }
        $existingTokens = $database->getData('token', 'forgotPassword');
        if($existingTokens === false) {
            return false;
        }
        if($existingTokens == null) {
            $existingTokens = array();
        }
        do {
            $token = $randomString->run(array('randomLength' => true, 'minLength' => 37, 'maxLength' => 136, 'length' => 50));
        } while(in_array(array('token'=>$token), $existingTokens));
        $date = new DateTime();
        $date = $date->format('Y-m-d H:i:s');
        $token = $database->escapeString($token);
        $date = $database->escapeString($date);
        $userID = $database->escapeString($userID);
        $result = $database->insertData('forgotPassword', 'token, requestDate, userID', "'{$token}', '{$date}', {$userID}");
        if($result == false) {
            return false;
        }
        return $this->getForgotPasswordByToken($token);
    }
    public function removeForgotPassword(forgotPassword $toRemove) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $toRemove->getID();
        $token = $toRemove->getToken();
        if(! is_numeric($id)) {
            return false;
        }
        $id = $database->escapeString($id);
        $cleanString = new general('cleanString');
        if(! $cleanString->functionsExists()) {
            return false;
        }
        $token = $cleanString->run(array('stringToClean' => $token));
        $token = $database->escapeString($token);
        $result = $database->removeData('forgotPassword', "id={$id} AND token='{$token}'");
        if($result == false) {
            return false;
        }
        return true;
    }
} 