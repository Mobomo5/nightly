<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 4/25/14
 * Time: 5:19 PM
 */

require_once(MODULE_INTERFACE_FILE);
require_once(ROUTER_OBJECT_FILE);
class home implements module {

    public function __construct() {
        // TODO: Implement __construct() method.
    }

    public static function getPageType() {
        return 'home';
    }

    public function noGUI() {
        // TODO: Implement noGUI() method.
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }

    public function getPageContent() {
        // TODO: Implement getPageContent() method.
    }
    public function getTitle() {
        return 'Home';
    }
    public function forceFourOhFour() {
        $params = router::getInstance()->getParameters(true);
        if(isset($params[1])) {
            return true;
        }
        return false;
    }
}