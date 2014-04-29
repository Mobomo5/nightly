<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/25/14
 * Time: 3:47 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
class permission {
    private $id;
    private $name;
    private $humanName;
    private $description;

    public function __construct($inID, $inName, $inHumanName, $inDescription) {
        if(! is_int($inID)) {
            return;
        }
        if($inID < 1) {
            return;
        }
        if(preg_match('/\s/', $inName)) {
            return;
        }
        $this->id = $inID;
        $this->name = $inName;
        $this->humanName = $inHumanName;
        $this->description = $inDescription;
    }
    public function getID(){
        return $this->id;
    }
    public function getName() {
        return $this->name;
    }
    public function setName($inName) {
        if(preg_match('/\s/', $inName)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if(! $database->updateTable('permission', 'permissionName = \'' . $database->escapeString(htmlspecialchars($inName)) .'\'', 'permissionID = ' . $this->id)) {
            return false;
        }
        return true;
    }
    public function getHumanName() {
        return $this->humanName;
    }
    public function setHumanName($inName) {
        if(preg_match('/\s/', $inName)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if(! $database->updateTable('permission', 'humanName = \'' . $database->escapeString(htmlspecialchars($inName)) . '\'', 'permissionID = ' . $this->id)) {
            return false;
        }
        return true;
    }
    public function getDescription() {
        return $this->description;
    }
    public function setDescription($inDescription) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if(! $database->updateTable('permission', 'permissionDescription = \'' . $database->escapeString(htmlspecialchars($inDescription)) . '\'', 'permissionID = ' . $this->id)) {
            return false;
        }
        return true;
    }
    public function canDo() {
        $user = currentUser::getUserSession();
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $results = $database->getData('canDo', 'permissionSet', 'permissionID = ' . $this->id . ' AND roleID = ' . $user->getRoleID());
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        if($results[0]['canDo'] == 0) {
            return false;
        }
        return true;
    }
    public function setCanDo($roleID, $inValue = false) {
        if(! is_bool($inValue)) {
            return false;
        }
        if(! is_int($roleID)) {
            return false;
        }
        if($roleID < 1) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if($inValue == true) {
            $canDo = 1;
        } else {
            $canDo = 0;
        }
        //Make sure that an entry exist for this permission and the specified role.
        $results = $database->getData('canDo', 'permissionSet', 'permissionID = ' . $this->id . ' AND roleID = ' . $roleID);
        if($results == false) {
            return false;
        }
        if($results == null) {
            return $this->insertNewCanDo($roleID, $inValue);
        }
        if(! $database->updateTable('permissionSet', 'canDo = ' . $canDo, 'permissionID = ' . $this->id . ' AND roleID = ' . $roleID)) {
            return false;
        }
        $this->canDo = $inValue;
        return true;
    }
    private function insertNewCanDo($roleID, $canDo = false) {
        if(! is_bool($canDo)) {
            return false;
        }
        if(! is_int($roleID)) {
            return false;
        }
        if($roleID < 1) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if($canDo == true) {
            $canDo = 1;
        } else {
            $canDo = 0;
        }
        if(! $database->insertData('permissionSet', 'canDo, roleID, permissionID', '' . $canDo . ', ' . $roleID . ', ' . $this->id)) {
            return false;
        }
        return true;
    }
}