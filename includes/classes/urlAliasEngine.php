<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 15/06/14
 * Time: 7:25 PM
 */

class urlAliasEngine {
    private $foundAliases;
    private static $instance;
    public static function getInstance() {
        if(! isset(self::$instance)) {
            self::$instance = new urlAliasEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundAliases = array();
    }
} 