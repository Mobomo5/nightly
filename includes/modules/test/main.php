<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 4/25/14
 * Time: 11:09 PM
 */
require_once(MODULE_INTERFACE_FILE);

class test implements module {

    public function __construct() {
        // TODO: Implement __construct() method.
    }

    public static function getPageType() {
        return 'test';
    }

    public function noGUI() {
        // TODO: Implement noGUI() method.
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }
}