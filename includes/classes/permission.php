<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/25/14
 * Time: 3:47 PM
 */
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

class permission {
    private $id;
    private $name;
    private $humanName;
    private $description;

    public function __construct($inID, $inName, $inHumanName, $inDescription) {
        if (!is_numeric($inID)) {
            return;
        }
        if ($inID < 1) {
            return;
        }
        $this->id = $inID;
        $this->name = preg_replace('/\s+/', '', $inName);;
        $this->humanName = strip_tags($inHumanName);
        $this->description = strip_tags($inDescription);
    }

    public function getID() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($inName) {
        $inName = preg_replace('/\s+/', '', $inName);
        $this->name = $inName;
    }

    public function getHumanName() {
        return $this->humanName;
    }

    public function setHumanName($inName) {
        $inName = strip_tags($inName);
        $this->humanName = $inName;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($inDescription) {
        $this->description = strip_tags($inDescription);
    }

    public function canDo() {
        $user = currentUser::getUserSession();
        $permissionEngine = permissionEngine::getInstance();
        return $permissionEngine->checkPermission($this, $user->getRoleID());
    }

    public function setCanDo($inValue = false) {
        if (!is_bool($inValue)) {
            return false;
        }
        $user = currentUser::getUserSession();
        $permissionEngine = permissionEngine::getInstance();
        return $permissionEngine->toggleCanDo($this, $user->getRoleID(), $inValue);
    }
}