<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 9/11/14
 * Time: 11:04 AM
 */
class ForgotPasswordEngine {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new ForgotPasswordEngine();
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
        $database = Database::getInstance();
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
        return new ForgotPassword($rawData[0]['requestID'], $rawData[0]['token'], $date, $rawData[0]['userID']);
    }
    public function getForgotPasswordByToken($token) {
        if(! is_string($token)) {
            return false;
        }
        $token = preg_replace('/\s+/', '', strip_tags($token));
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $cleanString = new cleanString();
        $token = $cleanString->run($token);
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
        return new ForgotPassword($rawData[0]['requestID'], $rawData[0]['token'], $date, $rawData[0]['userID']);
    }
    public function getForgotPasswordByUserID($userID) {
        if(! is_numeric($userID)) {
            return false;
        }
        $database = Database::getInstance();
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
        return new ForgotPassword($rawData[0]['requestID'], $rawData[0]['token'], $date, $rawData[0]['userID']);
    }
    public function generateNewForgotPassword($userID) {
        if(CurrentUser::getUserSession()->isLoggedIn()) {
            return false;
        }
        if(! is_numeric($userID)) {
            return false;
        }
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $randomString = new generateRandomString(50, true, 37, 136);
        $existingTokens = $database->getData('token', 'forgotPassword');
        if($existingTokens === false) {
            return false;
        }
        if($existingTokens === null) {
            $existingTokens = array();
        }
        do {
            $token = $randomString->run();
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
    public function removeForgotPassword(ForgotPassword $toRemove) {
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $toRemove->getID();
        $token = $toRemove->getToken();
        if(! is_numeric($id)) {
            return false;
        }
        $id = $database->escapeString($id);
        $cleanString = new cleanString();
        $token = $cleanString->run($token);
        $token = $database->escapeString($token);
        $result = $database->removeData('forgotPassword', "requestID={$id} AND BINARY token='{$token}'");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function removeExpiredTokens() {
        $maxAge = $this->getForgotPasswordTimePeriod();
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $result = $database->removeData('forgotPassword', "requestDate < DATE_SUB(NOW(), INTERVAL {$maxAge} MINUTE)");
        if(! $result) {
            return false;
        }
        return true;
    }
    public function resetUsersPassword($token, $userID, $chosenPassword, $chosenPasswordConfirmation) {
        if(! is_string($token)) {
            return false;
        }
        if(! is_numeric($userID)) {
            return false;
        }
        if($chosenPassword !== $chosenPasswordConfirmation) {
            return false;
        }
        if(strlen($chosenPassword) < $this->getMinimumPasswordLength()) {
            return false;
        }
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $forgotPassword1 = $this->getForgotPasswordByToken($token);
        if($forgotPassword1 === false) {
            return false;
        }
        $forgotPassword2 = $this->getForgotPasswordByUserID($userID);
        if($forgotPassword2 === false) {
            return false;
        }
        if(!$forgotPassword1->verify($forgotPassword2->getToken(), $forgotPassword2->getUserID())) {
            return false;
        }
        if(!$forgotPassword2->verify($forgotPassword1->getToken(), $forgotPassword1->getUserID())) {
            return false;
        }
        $newHash = Hasher::generateHash($chosenPassword);
        $newHash = $database->escapeString($newHash);
        $userID = $database->escapeString($forgotPassword1->getUserID());
        $result = $database->updateTable('user', "password = '$newHash'", "userID = $userID");
        if (!$result) {
            return false;
        }
        return true;
    }
    public function forgotPasswordIsOfValidAge(ForgotPassword $toCheck) {
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
        $variableEngine = VariableEngine::getInstance();
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
    public function getMinimumPasswordLength() {
        $variableEngine = VariableEngine::getInstance();
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