<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 19/04/14
 * Time: 11:05 AM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(MODULE_ENGINE_OBJECT_FILE);
require_once(LINK_OBJECT_FILE);
require_once(ROUTER_OBJECT_FILE);

class nodeEngine {
    private static $instance;
    private static $currentURL;
    private static $previousURL;
    private $sourceURL;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new nodeEngine();
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
        self::$currentURL = $_GET['p'];

        return self::$instance;
    }

    private function __construct() {
        //Do nothing.
    }

    private function determineAlias() {
        $database = database::getInstance();
        $page = self::$currentURL;
        $results = $database->getData('source', 'urlAlias', 'alias=\'' . $database->escapeString($page) . '\'');
        if ($results == null) {
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
        if ($asArray == true) {
            return explode('/', $this->sourceURL);
        }
        return $this->sourceURL;
    }

    public function getParameters($asArray = false) {
        if ($asArray == true) {
            return explode('/', self::$currentURL);
        }
        return self::$currentURL;
    }

    public function getPreviousParameters($asArray = false) {
        if (self::$previousURL == null) {
            return null;
        }
        if ($asArray == true) {
            return explode('/', self::$previousURL);
        }
        return self::$previousURL;
    }

    public function getNode() {
        $router = router::getInstance();
        $parameters = $router->getDecodedParameters(true);

        $moduleClass = $parameters[0];
        $moduleEngine = moduleEngine::getInstance();
        $moduleEngine->includeModule($moduleClass);
        //See the interfaces that the module implements, and make sure it implements node. If not, return 404.

        if (!class_exists($moduleClass)) {
            $moduleEngine->includeModule('404');
            return new fourOhFour();
        }
        $interfacesThatClassImplements = class_implements($moduleClass);

        if ($interfacesThatClassImplements === false) {
            $moduleEngine->includeModule('404');
            return new fourOhFour();
        }

        if (!in_array('node', $interfacesThatClassImplements)) {
            $moduleEngine->includeModule('404');
            return new fourOhFour();
        }

        $module = new $moduleClass();

        if ($module->noGUI()) {

        }

        $_SESSION['educaskPreviousPage'] = self::$currentURL;

        $pageTitle = $module->getTitle();
        if ($pageTitle == '404' && $moduleClass != 'fourOhFour') {
            $moduleEngine->includeModule('404');
            return new fourOhFour();
        }

        return $module;
    }
}