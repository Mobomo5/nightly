<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 9/5/2015
 * Time: 12:56 PM
 */

class request {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new request();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing
    }
    public function getParameter($inParameterName, parameterType $parameterType = parameterType::POST){}
    public function requestMethod() {}
}
abstract class parameterType {
    const all = 'all';
    const GET = 'GET';
    const POST = 'POST';
    const SESSION = 'SESSION';
}