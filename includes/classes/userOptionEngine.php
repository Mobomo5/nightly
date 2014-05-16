<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/16/14
 * Time: 10:16 AM
 */
require_once(USER_OPTION_OBJECT_FILE);

class userOptionEngine {
    private static $instance;
    private $checkedOptions;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new userOptionEngine();
        }
        return self::$instance;
    }

    public function setInstance() {

    }

    public function __construct() {
        // do nothing
    }

    public function getOption() {

    }

    public function setOption() {

    }

    public function addOption() {

    }

    public function deleteOption() {

    }

} 