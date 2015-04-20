<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 17/05/14
 * Time: 9:28 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(GENERAL_ENGINE_OBJECT_FILE);
require_once(MODULE_ENGINE_OBJECT_FILE);

class router {
    private static $instance;
    private static $currentURL;
    private static $previousURL;
    private $staticRoutes;
    private $sourceURL;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new router();
        }
        if (isset($_SESSION['educaskPreviousPage'])) {
            self::$previousURL = $_SESSION['educaskPreviousPage'];
        } else {
            self::$previousURL = null;
        }
        if (empty($_GET['p'])) {
            self::$currentURL = 'home';
            return self::$instance;
        }
        $function = new general('cleanString');
        $cleanURL = $function->run(array('stringToClean' => filter_var($_GET['p'], FILTER_SANITIZE_URL)));
        self::$currentURL = $cleanURL;
        return self::$instance;
    }
    private function __construct() {
        $this->staticRoutes = array();
        $this->sourceURL = '';
    }
    public function addRoute($parametersPattern, $moduleToRouteTo) {
        $parametersPattern = filter_var($parametersPattern, FILTER_SANITIZE_URL);
        if (!moduleEngine::getInstance()->moduleExists($moduleToRouteTo)) {
            return;
        }
        $this->staticRoutes[$parametersPattern] = $moduleToRouteTo;
    }
    private function determineAlias() {
        $database = database::getInstance();
        $page = self::$currentURL;
        $results = $database->getData('source', 'urlAlias', 'alias=\'' . $database->escapeString($page) . '\'');
        if ($results === null) {
            $this->sourceURL = $page;
            return false;
        }
        if (count($results) != 1) {
            $this->sourceURL = $page;
            return false;
        }
        $this->sourceURL = $results[0]['source'];
        return true;
    }
    public function getDecodedParameters($asArray = false) {
        $this->determineAlias();
        if ($asArray === true) {
            return explode('/', $this->sourceURL);
        }
        return $this->sourceURL;
    }
    public function getParameters($asArray = false) {
        if ($asArray === true) {
            return explode('/', self::$currentURL);
        }
        return self::$currentURL;
    }
    public function getPreviousParameters($asArray = false) {
        if (self::$previousURL === null) {
            return null;
        }
        if ($asArray === true) {
            return explode('/', self::$previousURL);
        }
        return self::$previousURL;
    }
    public function whichModuleHandlesRequest() {
        $params = $this->getDecodedParameters();
        if (isset($this->staticRoutes[$params])) {
            return $this->staticRoutes[$params];
        }
        $module = explode('/', $params);
        $module = $module[0];
        foreach ($this->staticRoutes as $route => $newModule) {
            if (! preg_match('#' . $route . '#', $params)) {
                continue;
            }
            $module = $newModule;
        }
        if (!moduleEngine::getInstance()->moduleExists($module)) {
            return 'fourOhFour';
        }
        return $module;
    }
    public static function moveCurrentParametersToPrevious() {
        $_SESSION['educaskPreviousPage'] = self::$currentURL;
    }
}