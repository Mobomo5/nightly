<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/23/14
 * Time: 2:52 PM
 */
require_once(MODULE_INTERFACE_FILE);
require_once(ROUTER_OBJECT_FILE);
class fourOhFour implements module {
    public function __construct() {
        // TODO: Implement __construct() method.
    }
    public static function getPageType() {
        return 'fourOhFour';
    }
    public function noGUI() {
        return false;
    }
    public function getReturnPage() {
        return '';
    }
    public function getPageContent() {
        $url = router::getInstance()->getParameters();
        $url = strip_tags($url);
        $content = "<p>Sorry, but the page {$url} was not found. Somebody probably thought it was a cookie and ate it.</p>";
        return $content;
    }
    public function getTitle() {
        return '404';
    }
    public function forceFourOhFour() {
        return false;
    }
}