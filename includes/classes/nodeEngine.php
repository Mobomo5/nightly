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

class nodeEngine {
    private static $instance;
    private static $currentURL;
    private static $previousURL;
    private $sourceURL;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new nodeEngine();
        }
        return self::$instance;
    }

    private function __construct() {
        //Do nothing.
    }
    public function getNode() {
        $parameters = $this->getDecodedParameters(true);

        $moduleClass = $parameters[0];
        $moduleEngine = moduleEngine::getInstance();
        $moduleEngine->includeModule($moduleClass);
//        if(! is_object($moduleClass)) {
//
//            $moduleEngine->includeModule('404');
//            return new fourOhFour();
//        }
//
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
            $link = $module->getReturnPage();
            //verify the variable given is a link object. If it is not, go to the home page.
            if (get_class($link) != 'link') {
                $past = $this->getPreviousParameters();
                if ($past == null) {
                    $link = new link('home');
                } else {
                    $link = new link($past); // haha, A Link to the Past!
                }
            }
            header('Location: ' . $link);
            exit();
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