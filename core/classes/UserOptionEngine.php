<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/16/14
 * Time: 10:16 AM
 */
class UserOptionEngine {
    private static $instance;
    private $checkedOptions;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new UserOptionEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->checkedOptions = array();
    }
    public function getOption($inOptionName) {
        $inOptionName = preg_replace('/\s+/', '', $inOptionName);
        if(isset($this->checkedOptions[$inOptionName]['userOptionObject'])) {
            return $this->checkedOptions[$inOptionName]['userOptionObject'];
        }
        // open the db
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $inOptionName = $db->escapeString($inOptionName);
        // run the query
        $results = $db->getData('*', 'userOption', 'optionName = \'' . $inOptionName . '\'');
        if (!$results) {
            return false;
        }
        if (sizeof($results) === 0) {
            return false;
        }
        //get the first value and return the object
        $id = $results[0]['id'];
        $computerName = $results[0]['optionName'];
        $humanName = $results[0]['humanName'];
        $description = $results[0]['description'];
        $option = new UserOption($id, $computerName, $humanName, $description);
        $this->checkedOptions[$inOptionName]['userOptionObject'] = $option;
        return $option;
    }
    public function getUserValue($optionName, $userID) {
        if(! is_numeric($userID)) {
            return false;
        }
        $userID = intval($userID);
        if($userID < 1) {
            return false;
        }
        $option = $this->getOption($optionName);
        if($option === false) {
            return false;
        }
        // is it stored already?
        if (isset($this->checkedOptions[$option->getComputerName()][$userID])) {
            return $this->checkedOptions[$option->getComputerName()][$userID];
        }
        // get the db
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        // is the option enabled?
        $result = $db->getData('s.value', 'userOption u, userOptionSet s', 'u.optionID = s.optionID AND u.optionID = ' . $db->escapeString($option->getID()) . ' AND s.userID = ' . $userID);
        if($result === false) {
            return false;
        }
        if($result === null) {
            return false;
        }
        if(count($result) > 1) {
            return false;
        }
        $value = $result[0]['value'];
        // store the value
        $this->checkedOptions[$option->getComputerName()][$userID] = $value;
        //return the value
        return $value;
    }
    public function getCurrentUserValue($optionName) {
        $userID = intval(CurrentUser::getUserSession()->getID());
        return $this->getUserValue($optionName, $userID);
    }
    public function setUserOptionValue(UserOption $option, $userID, $value) {
        if(! is_numeric($userID)) {
            return false;
        }
        $userID = intval($userID);
        if($userID < 1) {
            return false;
        }
        // check permissions
        $permEng = PermissionEngine::getInstance();
        if(! $permEng->currentUserCanDo('canSetUserOptionValues')) {
            return false;
        }
        // get db
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $optionID = $db->escapeString($option->getID());
        $userID = $db->escapeString($userID);
        $value = $db->escapeString($value);
        $data = $db->getData('optionSetID', 'userOptionSet', 'optionID = ' . $optionID . ' AND userID=' . $userID);
        if(! is_array($data)) {
            return $this->insertUserOptionValue($option, $userID, $value);
        }
        if(count($data) > 1) {
            $db->removeData('userOptionSet','optionID = ' . $optionID . ' AND userID=' . $userID);
            return $this->insertUserOptionValue($option, $userID, $value);
        }
        $optionSetID = $db->escapeString($data[0]['optionSetID']);
        $results = $db->updateTable('userOptionSet', 'value = \'' . $value . '\'', 'optionSetID = '. $optionSetID . ' AND optionID = ' . $optionID . ' AND userID=' . $userID);
        if ($results === false) {
            return false;
        }
        return true;
    }
    private function insertUserOptionValue(UserOption $option, $userID, $value) {
        if(! is_numeric($userID)) {
            return false;
        }
        $userID = intval($userID);
        if($userID < 1) {
            return false;
        }
        // check permissions
        $permEng = PermissionEngine::getInstance();
        if(! $permEng->currentUserCanDo('canSetUserOptionValues')) {
            return false;
        }
        // get db
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $optionID = $db->escapeString($option->getID());
        $userID = $db->escapeString($userID);
        $value = $db->escapeString($value);
        $results = $db->insertData('userOptionSet', 'value, userID, optionID', "'{$value}', {$userID}, {$optionID}");
        if($results === false) {
            return false;
        }
        return true;
    }
    public function addOption(UserOption $inOption) {
        // check permissions
        $permEng = PermissionEngine::getInstance();
        if(! $permEng->currentUserCanDo('canAddUserOptions')) {
            return false;
        }
        // get db
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $inOptionName = preg_replace('/\s+/', '', $inOption->getComputerName());
        $inHumanName = strip_tags($inOption->getHumanName());
        $inOptionDescription = strip_tags($inOption->getDescription());
        // escape
        $inOptionName = $db->escapeString($inOptionName);
        $inHumanName = $db->escapeString($inHumanName);
        $inOptionDescription = $db->escapeString($inOptionDescription);
        $results = $db->insertData('userOption', 'optionName, humanName, description', '\'' . $inOptionName . '\', \'' . $inHumanName . '\', \'' . $inOptionDescription . '\'');
        if ($results === false) {
            return false;
        }
        return true;
    }
    public function deleteOption(UserOption $option) {
        // check permissions
        $permEng = PermissionEngine::getInstance();
        if(! $permEng->currentUserCanDo('canDeleteUserOptions')) {
            return false;
        }
        // get db
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $results = $db->removeData('userOption', 'optionName = \'' . $db->escapeString($option->getComputerName()) . '\' AND optionID = ' . $db->escapeString($option->getID()));
        if ($results === false) {
            return false;
        }
        return true;
    }
    public function editOption(UserOption $inOption) {
        // check permissions
        $permEng = PermissionEngine::getInstance();
        if(! $permEng->currentUserCanDo('canEditUserOptions')) {
            return false;
        }
        // get db
        $db = Database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $id = $db->escapeString(intval($inOption->getID()));
        $computerName = $db->escapeString(preg_replace('/\s+/', '', $inOption->getComputerName()));
        $humanName = $db->escapeString(strip_tags($inOption->getHumanName()));
        $description = $db->escapeString(strip_tags($inOption->getDescription()));
        $result = $db->updateTable('userOption', "optionName='{$computerName}', humanName='{$humanName}', optionDescription='{$description}'", "optionID={$id}");
        if($result === false) {
            return false;
        }
        return true;
    }
}