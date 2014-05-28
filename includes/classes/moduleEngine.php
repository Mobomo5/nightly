<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/19/14
 * Time: 4:15 PM
 */
require_once(VALIDATOR_OBJECT_FILE);

class moduleEngine {
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new moduleEngine();
        }

        return self::$instance;
    }

    private function __construct() {
        //Do nothing.
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
            require_once(EDUCASK_ROOT . '/includes/modules/404/main.php');
            return false;
        }
        $validator = new validator('file');
        if (!$validator->validatorExists()) {
            return false;
        }
        $file = '/includes/modules/' . $moduleName . '/main.php';
        if(! $validator->validate($file)) {
            return false;
        }
        require_once(EDUCASK_ROOT . $file);
        if(! class_exists($moduleName)) {
            return false;
        }
        $interfacesImplemented = class_implements($moduleName);
        if(! in_array('module', $interfacesImplemented)) {
            return false;
        }
        return true;
    }

    public function addModule($name, $humanName, $enabled = 1) {

        // check permissions
        $permEng = permissionEngine::getInstance();
        $perm = $permEng->getPermission('canAddModule');
        if (!$perm->canDo()) {
            return false;
        }

        // validate
        $nameVal = new validator('optionName');
        if (!$nameVal->validate($name)) {
            return false;
        }

        //get db
        $db = database::getInstance();

        // escape
        $name = $db->escapeString($name);
        $humanName = $db->escapeString($humanName);

        $results = $db->insertData('modules', 'moduleName, humanName, enabled', '\'' . $name . '\',\'' . $humanName . '\',\'' . $enabled . '\'');
        if (!$results) {
            return false;
        }
        return true;
    }
}