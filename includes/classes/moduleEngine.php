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
        $this->actionEvents = array();
        $this->filterEvents = array();
    }
    public function moduleExists($moduleName) {
        $validator = new validator('dir');
        if(! $validator->validatorExists()) {
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
            return;
        }

        //The module's main.php must contain a function that will give the name of the page
        require_once(EDUCASK_ROOT . '/includes/modules/' . $moduleName . '/main.php');
    }
}