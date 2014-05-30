<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 4/25/14
 * Time: 11:09 PM
 */
require_once(MODULE_INTERFACE_FILE);
require_once(ROUTER_OBJECT_FILE);
class test implements module {
    public function __construct() {
    }

    public static function getPageType() {
        return 'test';
    }

    public function noGUI() {
        return false;
    }

    public function getReturnPage() {
        return null;
    }

    public function getPageContent() {
        return 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum';
    }
    public function getTitle() {
        return 'Test';
    }
    public function forceFourOhFour() {
        $params = router::getInstance()->getParameters(true);
        if(isset($params[1])) {
            return true;
        }
        return false;
    }
}