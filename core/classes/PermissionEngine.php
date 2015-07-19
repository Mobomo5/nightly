<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 27/04/14
 * Time: 7:26 PM
 */
class PermissionEngine {
    private static $instance;
    private $permissionsChecked;
    private $retrievedPermissions;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new PermissionEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->permissionsChecked = array();
        $this->retrievedPermissions = array();
    }
    public function getPermission($inPermissionName) {
        $inPermissionName = preg_replace('/\s+/', '', $inPermissionName);
        if (!empty($this->retrievedPermissions[$inPermissionName])) {
            return $this->retrievedPermissions[$inPermissionName];
        }
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inPermissionName = $database->escapeString(htmlspecialchars($inPermissionName));
        $results = $database->getData('permissionID, permissionName, humanName, permissionDescription', 'permission', 'permissionName = \'' . $inPermissionName . '\'');
        if ($results === false) {
            return false;
        }
        if ($results === null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        $permission = new Permission($results[0]['permissionID'], $results[0]['permissionName'], $results[0]['humanName'], $results[0]['permissionDescription']);
        $this->retrievedPermissions[$inPermissionName] = $permission;
        return $permission;
    }
    public function checkPermission(Permission $inPermission, $inRoleID = GUEST_ROLE_ID) {
        if (!is_numeric($inRoleID)) {
            return false;
        }
        if ($inRoleID < 1) {
            return false;
        }
        if (isset($this->permissionsChecked[$inPermission->getName()][$inRoleID])) {
            return $this->permissionsChecked[$inPermission->getName()][$inRoleID];
        }
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $results = $database->getData('canDo', 'permissionSet', 'permissionID = ' . $inPermission->getID() . ' AND roleID = ' . $inRoleID);
        if ($results === false) {
            return false;
        }
        if ($results === null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        if ($results[0]['canDo'] === 0) {
            $this->permissionsChecked[$inPermission->getName()][$inRoleID] = false;
            return false;
        }
        $this->permissionsChecked[$inPermission->getName()][$inRoleID] = true;
        return true;
    }
    public function addPermission(Permission $inPermission) {
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inName = $database->escapeString(htmlspecialchars(preg_replace('/\s+/', '', $inPermission->getName())));
        $inHumanName = $database->escapeString(htmlspecialchars(strip_tags($inPermission->getHumanName())));
        $inDescription = $database->escapeString(htmlspecialchars(strip_tags($inPermission->getDescription())));
        if (!$database->insertData('permission', 'permissionName, humanName, permissionDescription', '\'' . $inName . '\', \'' . $inHumanName . '\', \'' . $inDescription . '\'')) {
            return false;
        }
        return true;
    }
    public function savePermission(Permission $inPermission) {
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inName = $database->escapeString(htmlspecialchars(preg_replace('/\s+/', '', $inPermission->getName())));
        $inHumanName = $database->escapeString(htmlspecialchars(strip_tags($inPermission->getHumanName())));
        $inDescription = $database->escapeString(htmlspecialchars(strip_tags($inPermission->getDescription())));
        if (!$database->updateTable('permission', "permissionName='{$inName}', humanName='{$inHumanName}', permissionDescription='{$inDescription}'", "permissionID={$inPermission->getID()}")) {
            return false;
        }
        return true;
    }
    public function toggleCanDo(Permission $permissionToSet, $roleID, $inCanDo = false) {
        if (!is_bool($inCanDo)) {
            return false;
        }
        if (!is_int($roleID)) {
            return false;
        }
        if ($roleID < 1) {
            return false;
        }
        $canChangePermissions = $this->currentUserCanDo('canChangePermissions');
        if (!$canChangePermissions) {
            return false;
        }
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        if ($inCanDo === true) {
            $canDo = 1;
        } else {
            $canDo = 0;
        }
        //Make sure that an entry exist for this permission and the specified role.
        $results = $database->getData('canDo', 'permissionSet', 'permissionID = ' . $permissionToSet->getID() . ' AND roleID = ' . $roleID);
        if ($results === false) {
            return false;
        }
        if ($results === null) {
            return $this->insertNewCanDo($roleID, $canDo);
        }
        if (!$database->updateTable('permissionSet', 'canDo = ' . $canDo, 'permissionID = ' . $permissionToSet->getID() . ' AND roleID = ' . $roleID)) {
            return false;
        }
        return true;
    }
    private function insertNewCanDo($roleID, $permissionID, $canDo = 0) {
        if (!is_int($canDo)) {
            return false;
        }
        if ($canDo != 0 || $canDo != 1) {
            return false;
        }
        if (!is_int($roleID)) {
            return false;
        }
        if ($roleID < 1) {
            return false;
        }
        if (!is_int($permissionID)) {
            return false;
        }
        if ($permissionID < 1) {
            return false;
        }
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        if (!$database->insertData('permissionSet', 'canDo, roleID, permissionID', '' . $canDo . ', ' . $roleID . ', ' . $permissionID)) {
            return false;
        }
        return true;
    }
    public function currentUserCanDo($inPermissionName) {
        $perm = $this->getPermission($inPermissionName);
        if (!$perm) {
            return false;
        }
        if (!$this->checkPermission($perm, CurrentUser::getUserSession()->getRoleID())) {
            return false;
        }
        return true;
    }
}