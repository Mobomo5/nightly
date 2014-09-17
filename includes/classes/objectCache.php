<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 16/09/2014
 * Time: 11:44 AM
 */
class objectCache {
    private $cache;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new objectCache();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing
    }
}