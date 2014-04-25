<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/25/14
 * Time: 3:47 PM
 */
require_once(DATABASE_OBJECT_FILE);
class permission {
    private $id;
    private $name;
    private $humanName;
    private $description;
    private $roleID;
    private $canDo;

    public function __construct($inID, $inName, $inHumanName, $inDescription, $inRoleID = GUEST_ROLE_ID, $canDo = false) {
        if(! is_int($inID)) {
            return;
        }
        if(! is_int($inRoleID)) {
            return;
        }
        if(! is_bool($canDo)) {
            return;
        }
        $this->id = $inID;
        $this->name = $inName;
        $this->humanName = $inHumanName;
        $this->description = $inDescription;
        $this->roleID = $inRoleID;
        $this->canDo = $canDo;
    }
    public function getID(){
        return $this->id;
    }
    public function getName() {
        return $this->name;
    }
    public function getHumanName() {
        return $this->humanName;
    }
    public function getDescription() {
        return $this->description;
    }
    public function getRoleID() {
        return $this->roleID;
    }
    public function canDo() {
        if(! $this->canDo) {
            return false;
        }
        return true;
    }
    public function setCanDo($inValue = false) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if(! is_bool($inValue)) {
            return false;
        }
        if($inValue) {
            $canDo = 1;
        } else {
            $canDo = 0;
        }
        if(! $database->updateTable('permissionSet', 'canDo = ' . $canDo, 'permissionID = ' . $this->id . ' AND roleID = ' . $this->roleID)) {
            return false;
        }
        return true;
    }
}