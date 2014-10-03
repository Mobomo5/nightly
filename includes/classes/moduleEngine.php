<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/19/14
 * Time: 4:15 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(VALIDATOR_OBJECT_FILE);
class moduleEngine {
    private static $instance;
    private $foundModules;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new moduleEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundModules = array();
    }
    public function moduleExists($moduleName) {
        $moduleName = str_replace('..', '', $moduleName);
        if ($moduleName == '/') {
            return false;
        }
        if ($moduleName == '') {
            return false;
        }
        if ($moduleName == null) {
            return false;
        }
        $moduleData = $this->getRawModuleDataFromDatabase($moduleName);
        if ($moduleData == false) {
            return false;
        }
        if ($moduleData['enabled'] == '0') {
            return false;
        }
        $validator = new validator('dir');
        if (!$validator->validatorExists()) {
            return false;
        }
        $module = '/includes/modules/' . $moduleName;
        if (!$validator->validate($module)) {
            return false;
        }
        return true;
    }
    public function includeModule($moduleName) {
        if (!$this->moduleExists($moduleName)) {
            $moduleName = 'fourOhFour';
        }
        $validator = new validator('fileV');
        if (!$validator->validatorExists()) {
            return false;
        }
        $file = '/includes/modules/' . $moduleName . '/main.php';
        if (!$validator->validate($file)) {
            return false;
        }
        require_once(EDUCASK_ROOT . $file);
        if (!class_exists($moduleName)) {
            return false;
        }
        $interfacesImplemented = class_implements($moduleName);
        if (!in_array('module', $interfacesImplemented)) {
            return false;
        }
        return true;
    }
    public function addModule($name, $humanName, $enabled = false) {
        if(! is_bool($enabled)) {
            return false;
        }
        // check permissions
        $permEng = permissionEngine::getInstance();
        if(! $permEng->currentUserCanDo('canAddModule')) {
            return false;
        }
        //get db
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        // escape
        $name = preg_replace('.', '', $name);
        $name = preg_replace('\/', '', $name);
        $name = $db->escapeString(preg_replace('/\s/', '', strip_tags($name)));
        $humanName = $db->escapeString(strip_tags($humanName));
        if($enabled == true) {
            $enabledVal = 1;
        } else {
            $enabledVal = 0;
        }
        $results = $db->insertData('modules', 'moduleName, humanName, enabled', '\'' . $name . '\',\'' . $humanName . '\',\'' . $enabledVal . '\'');
        if (!$results) {
            return false;
        }
        return true;
    }
    public function modifyModule($moduleID, $humanName, $enabled) {
        if(! is_numeric($moduleID)) {
            return false;
        }
        if(! is_bool($enabled)) {
            return false;
        }
        // check permissions
        $permEng = permissionEngine::getInstance();
        if(! $permEng->currentUserCanDo('canModifyModules')) {
            return false;
        }
        //get db
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        // escape
        $humanName = $db->escapeString(strip_tags($humanName));
        if($enabled == true) {
            $enabledVal = 1;
        } else {
            $enabledVal = 0;
        }
        $results = $db->updateTable('module', "humanName='{$humanName}', enabled={$enabledVal}", "moduleID={$moduleID}");
        if (!$results) {
            return false;
        }
        return true;
    }
    public function getRawModuleDataFromDatabase($inModuleIdentifier) {
        $inModuleIdentifier = preg_replace('/\s+/', '', strip_tags($inModuleIdentifier));
        foreach ($this->foundModules as $module) {
            if ($module['moduleID'] == $inModuleIdentifier) {
                return $module;
            }
            if ($module['moduleName'] == $inModuleIdentifier) {
                return $module;
            }
        }
        if (is_numeric($inModuleIdentifier)) {
            $moduleData = $this->getRawDataBasedOnID($inModuleIdentifier);
            if ($moduleData == false) {
                return false;
            }
            $this->foundModules[] = $moduleData;
            return $moduleData;
        }
        $moduleData = $this->getRawDataBasedOnName($inModuleIdentifier);
        if ($moduleData == false) {
            return false;
        }
        $this->foundModules[] = $moduleData;
        return $moduleData;
    }
    private function getRawDataBasedOnID($inModuleID) {
        if (!is_numeric($inModuleID)) {
            return false;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inModuleID = $database->escapeString($inModuleID);
        $success = $database->getData('moduleID, moduleName, humanName, enabled', 'module', "moduleID={$inModuleID}");
        if ($success == false) {
            return false;
        }
        if ($success == null) {
            return false;
        }
        if (count($success) > 1) {
            return false;
        }
        return $success[0];
    }
    private function getRawDataBasedOnName($inModuleName) {
        $inModuleName = preg_replace('/\s+/', '', strip_tags($inModuleName));
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inModuleName = $database->escapeString($inModuleName);
        $success = $database->getData('moduleID, moduleName, humanName, enabled', 'module', "moduleName='{$inModuleName}'");
        if ($success == false) {
            return false;
        }
        if ($success == null) {
            return false;
        }
        if (count($success) > 1) {
            return false;
        }
        return $success[0];
    }
}