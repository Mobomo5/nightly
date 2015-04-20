<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 12:58 PM
 */
require_once(ROLE_OBJECT_FILE);
require_once(DATABASE_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
class roleEngine {
    private static $instance;
    private $foundRoles;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new roleEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundRoles = array();
    }
    public function getRoleByID($roleID) {
        if (!is_numeric($roleID)) {
            return false;
        }
        if(isset($this->foundRoles[$roleID])) {
            return $this->foundRoles[$roleID];
        }
        // query
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $roleID = $db->escapeString($roleID);
        $results = $db->getData('*', 'role', 'roleID = ' . $roleID);
        if (!$results) {
            return false;
        }
        $role = new role($results[0]['roleID'], $results[0]['roleName'], $results[0]['description']);
        $this->foundRoles[$role->getID()] = $role;
        return $role;
    }
    public function getRoleByName($inName) {
        $inName = strip_tags($inName);
        foreach($this->foundRoles as $toCheck) {
            if($toCheck->getName() != $inName) {
                continue;
            }
            return $toCheck;
        }
        // query
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $inName = $db->escapeString($inName);
        $results = $db->getData('*', 'role', 'roleName = \'' . $inName . '\'');
        if (!$results) {
            return false;
        }
        $role = new role($results[0]['roleID'], $results[0]['roleName'], $results[0]['description']);
        $this->foundRoles[$role->getID()] = $role;
        return $role;
    }
    public function setRole(role $inRole) {
        // check permissions
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanAlterRoles')) {
            return false;
        }
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $roleID = $db->escapeString($inRole->getID());
        $roleName = $db->escapeString($inRole->getName());
        $roleDesc = $db->escapeString($inRole->getDescription());
        $results = $db->updateTable('role', 'roleName=\'' . $roleName . '\', description=\'' . $roleDesc . '\'', 'roleID=' . $roleID);
        if (!$results) {
            return false;
        }
        return true;
    }
    public function addRole(role $inRole) {
        // check permissions
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanCreateRoles')) {
            return false;
        }
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $roleName = $db->escapeString($inRole->getName());
        $roleDesc = $db->escapeString($inRole->getDescription());
        if ($roleDesc === '') {
            $results = $db->insertData('role', 'roleName', '\'' . $roleName . '\'');
        } else {
            $results = $db->insertData('role', 'roleName, description', '\'' . $roleName . '\',\'' . $roleDesc . '\'');
        }
        if (!$results) {
            return false;
        }
        return true;
    }
    public function deleteRole(role $roleToDelete) {
        // check permissions
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanDeleteRoles')) {
            return false;
        }
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $roleID = $db->escapeString($roleToDelete->getID());
        $roleName = $db->escapeString($roleToDelete->getName());
        $results = $db->removeData('role', 'roleID = ' . $roleID . ' AND roleName = \'' . $roleName . '\'');
        if (!$results) {
            return false;
        }
        return true;
    }
}