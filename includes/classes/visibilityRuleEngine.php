<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 26/05/14
 * Time: 7:54 PM
 */

//Possible template for future. Do not use.
class visibilityRuleEngine {
    private static $instance;
    public static function getInstance() {
        if(! isset(self::$instance)) {
            self::$instance = new visibilityRuleEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing.
    }
    public function getRule($ruleID) {

    }
} 