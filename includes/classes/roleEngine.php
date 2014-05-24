<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 12:58 PM
 */
require_once(ROLE_OBJECT_FILE);

class roleEngine {
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new roleEngine();
        }
        return self::$instance;
    }

    /**
     *
     */
    private function __construct() {

    }

    /**
     * @param $roleID
     * @return role
     */
    public function getRoleByID($roleID) {
        if (!is_numeric($roleID)) {
            return new role();
        }
        if (intval($roleID) < 0) {
            return new role();;
        }

        // query
        $db = database::getInstance();
        $results = $db->getData('*', 'role', 'roleID = \'' . $roleID . '\'');
        echo $db->getError();
        if (!$results) {
            return new role();;
        }
        $role = new role($results[0]['roleID'], $results[0]['roleName'], $results[0]['description']);
        if (!$role) {
            return new role();;
        }

        return $role;
    }

    /**
     * @param $inName
     * @return role
     */
    public function getRoleByName($inName) {
        // query
        $db = database::getInstance();
        $results = $db->getData('*', 'role', 'roleName = \'' . $inName . '\'');
        echo $db->getError();
        if (!$results) {
            return new role();;
        }
        $role = new role($results[0]['roleID'], $results[0]['roleName'], $results[0]['description']);
        if (!$role) {
            return new role();;
        }

        return $role;
    }

    /**
     * @param role $inRole
     * @return bool
     */
    public function setRole(role $inRole) { // check permissions
        if (!permissionEngine::getInstance()->checkPermissionByName('userCanAlterRoles')) {
            return false;
        }
        $db = database::getInstance();

        $roleID = $inRole->getId();
        $roleName = $db->escapeString($inRole->getName());
        $roleDesc = $db->escapeString($inRole->getDescription());
        $results = $db->updateTable('role', 'roleName=\'' . $roleName . '\', description=\'' . $roleDesc . '\'', 'roleID=' . $roleID);
        echo $db->getError();
        if (!$results) {
            return false;
        }

        return true;


    }

    /**
     * @param role $inRole
     * @return bool|int the roleID of the added role
     */
    public function addRole(role $inRole) {
        // check permissions
        if (!permissionEngine::getInstance()->checkPermissionByName('userCanCreateRoles')) {
            return false;
        }
        $roleName = $inRole->getName();
        $roleDesc = $inRole->getDescription();

        $db = database::getInstance();

        if ($roleDesc == '') {
            $results = $db->insertData('role', 'roleName', '\'' . $roleName . '\'');

        } else {
            $results = $db->insertData('role', 'roleName, description', '\'' . $roleName . '\',\'' . $roleDesc . '\'');
        }
        if (!$results) {
            return false;
        }
        $results = $db->getData('*', 'role', 'roleName = \'' . $roleName . '\'');

        if (!$results) {
            return false;
        }
        return $results[0]['roleID'];

    }

    /**
     *
     */
    public function deleteRole(role $roleToDelete) {
        // check permissions
        if (!permissionEngine::getInstance()->checkPermissionByName('userCanDeleteRoles')) {
            return false;
        }

        $roleID = $roleToDelete->getId();
        $roleName = $roleToDelete->getName();
        $roleDescription = $roleToDelete->getDescription();

        $db = database::getInstance();
        if ($roleDescription == '') {
            $results = $db->removeData('role', 'roleName = \'' . $roleName . '\'');

        } else {
            $results = $db->removeData('role', 'roleName = \'' . $roleName . '\' AND description = \'' . $roleDescription . '\'');
        }
        if (!$results) {
            return false;
        }
        return true;

    }

} 