<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/23/14
 * Time: 2:52 PM
 */
require_once(MODULE_INTERFACE_FILE);
class fiveHundred implements module {
    public function __construct() {
        // TODO: Implement __construct() method.
    }
    public static function getPageType() {
        return 'fiveHundred';
    }
    public function noGUI() {
        return false;
    }
    public function getReturnPage() {
        return '';
    }
    public function getPageContent() {
        $content = "<p>Sorry, but I had problems rendering the page you requested. Please try again.</p>";
        return $content;
    }
    public function getTitle() {
        return '500: Internal Server Error';
    }
    public function forceFourOhFour() {
        return false;
    }
}