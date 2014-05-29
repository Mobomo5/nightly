<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/23/14
 * Time: 2:52 PM
 */
require_once(MODULE_INTERFACE_FILE);

class fourOhFour implements module {

    public function __construct() {
        // TODO: Implement __construct() method.
    }

    public static function getPageType() {
        return 'fourOhFour';
    }

    public function noGUI() {
        // TODO: Implement noGUI() method.
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }
}