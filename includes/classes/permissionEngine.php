<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 27/04/14
 * Time: 7:26 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(PERMISSION_OBJECT_FILE);
class permissionEngine {
    private static $instance;
    private $permissionsChecked;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new permissionEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->permissionsChecked = array();
    }
    public function getPermission($inPermissionName) {
        if(preg_match('/\s/', $inPermissionName)) {
            return false;
        }
        if(!empty($this->permissionsChecked[$inPermissionName])) {
            return $this->permissionsChecked[$inPermissionName];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $results = $database->getData('permissionID, permissionName, humanName, permissionDescription', 'permission', 'permissionName = \'' . $database->escapeString(htmlspecialchars($inPermissionName)) . '\'');
        if($results == false) {
            return false;
        }
        if($results == null) {
            return null;
        }
        if(count($results > 1)) {
            return false;
        }
        $permission = new permission($results[0]['permissionID'], $results[0]['permissionName'], $results[0]['humanName'], $results[0]['permissionDescription']);
        $this->permissionsChecked[$inPermissionName] = $permission;
        return $permission;
    }
    public function checkPermission($inPermissionName) {
        $permission = $this->getPermission($inPermissionName);
        if($permission == null) {
            return false;
        }
        if($permission == false) {
            return false;
        }
        return $permission->canDo();
    }
    public function addPermission(permission $inPermission) {

    }
}