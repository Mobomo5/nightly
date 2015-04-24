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
require_once(HASHER_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(VARIABLE_OBJECT_FILE);
require_once(VARIABLE_ENGINE_OBJECT_FILE);
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
        if($rawData === false) {
            return false;
        }
        if($rawData === null) {
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
        $rawData = $database->getData('*', 'forgotPassword', "BINARY token='{$id}'");
        if($rawData === false) {
            return false;
        }
        if($rawData === null) {
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
        if($rawData === false) {
            return false;
        }
        if($rawData === null) {
            return false;
        }
        if(count($rawData) > 1) {
            return false;
        }
        $date = new DateTime($rawData[0]['requestDate']);
        return new forgotPassword($rawData[0]['requestID'], $rawData[0]['token'], $date, $rawData[0]['userID']);
    }
    public function generateNewForgotPassword($userID) {
        if(currentUser::getUserSession()->isLoggedIn()) {
            return false;
        }
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
        if($existingTokens === null) {
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
        if($result === false) {
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
        $result = $database->removeData('forgotPassword', "requestID={$id} AND token='{$token}'");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function removeExpiredTokens() {
        $maxAge = $this->getForgotPasswordTimePeriod();
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $result = $database->removeData('forgotPassword', "requestDate < DATE_SUB(NOW(), INTERVAL {$maxAge} MINUTE)");
        if(! $result) {
            return false;
        }
        return true;
    }
    public function resetUsersPassword(forgotPassword $forgotPassword1, forgotPassword $forgotPassword2, $chosenPassword, $chosenPasswordConfirmation) {
        if($chosenPassword !== $chosenPasswordConfirmation) {
            return false;
        }
        if(strlen($chosenPassword) < $this->getMinimumPasswordLength()) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if(!$forgotPassword1->verify($forgotPassword2->getToken(), $forgotPassword2->getUserID())) {
            return false;
        }
        if(!$forgotPassword2->verify($forgotPassword1->getToken(), $forgotPassword1->getUserID())) {
            return false;
        }
        $hasher = new hasher();
        $newHash = $hasher->generateHash($chosenPassword);
        $newHash = $database->escapeString($newHash);
        $userID = $database->escapeString($forgotPassword1->getUserID());
        $result = $database->updateTable('user', "password = '$newHash'", "userID = $userID");
        if (!$result) {
            return false;
        }
        return true;
    }
    public function forgotPasswordIsOfValidAge(forgotPassword $toCheck) {
        $period = $this->getForgotPasswordTimePeriod();
        $requestDate = clone $toCheck->getRequestDate();
        $currentTime = new DateTime();
        $validTill = $requestDate->add(DateInterval::createFromDateString($period . ' minutes'));
        if($currentTime >= $validTill) {
            return false;
        }
        return true;
    }
    public function getForgotPasswordTimePeriod() {
        $variableEngine = variableEngine::getInstance();
        $period = $variableEngine->getVariable('forgotPasswordPeriod');
        $default = 10;
        if($period === null) {
            return $default;
        }
        if($period === false) {
            return $default;
        }
        if(! is_numeric($period->getValue())) {
            return $default;
        }
        return intval($period->getValue());
    }
    public function setForgotPasswordTimePeriod($inTime) {
        if(! is_int($inTime)) {
            return false;
        }
        $variableEngine = variableEngine::getInstance();
        $period = $variableEngine->getVariable('forgotPasswordPeriod');
        $period->setValue($inTime);
        $success = $period->save();
        if($success === false) {
            return false;
        }
        return true;
    }
    public function getMinimumPasswordLength() {
        $variableEngine = variableEngine::getInstance();
        $minimumPasswordLength = $variableEngine->getVariable('minimumPasswordLength');
        $default = 10;
        if($minimumPasswordLength === null) {
            return $default;
        }
        if($minimumPasswordLength === false) {
            return $default;
        }
        if(! is_numeric($minimumPasswordLength->getValue())) {
            return $default;
        }
        return intval($minimumPasswordLength->getValue());
    }
} 