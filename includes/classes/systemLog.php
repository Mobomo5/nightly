<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 07/01/14
 * Time: 12:45 PM
 */
require_once(DATABASE_OBJECT_FILE);
class logger {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new logger();
        }

        return self::$instance;
    }
    private function __construct(){}
    public function logIt($userID, $inMessage) {
        if(! is_numeric($userID)) {
            return false;
        }
        $database = database::getInstance();
        $database->connect();
        if(! $database->isConnected()) {
            return false;
        }
        $message = $database->escapeString(htmlspecialchars($inMessage));
        $database->insertData('systemLog', 'user, message', $userID . ', \'' . $message . '\'');
    }
}