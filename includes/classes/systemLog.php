<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 07/01/14
 * Time: 12:45 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(LOG_ENTRY_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
class logger {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new logger();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing.
    }
    public function logIt(logEntry $entry) {
        $type = $entry->getType();
        $message = $entry->getMessage();
        $userID = $entry->getUserID();
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        $message = $db->escapeString($message);
        $results = $db->insertData('systemLog', 'message, type, userID', '\'' . $message . '\', \'' . $type . '\', \'' . $userID . '\'');
        if (!$results) {
            return false;
        }
        return true;
    }
    public function getLog($level = 'all') {
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanViewLog')) {
            return false;
        }
        $db = database::getInstance();
        if(! $db->isConnected()) {
            return false;
        }
        if (strcmp($level, 'info')) {
            $results = $db->getData('*', 'systemLog', 'type = \'info\'');
        } elseif (strcmp($level, 'warning')) {
            $results = $db->getData('*', 'systemLog', 'type = \'warning\'');
        } elseif (strcmp($level, 'neutral')) {
            $results = $db->getData('*', 'systemLog', 'type = \'neutral\'');
        } else {
            $results = $db->getData('*', 'systemLog');
        }
        if($results === false) {
            return false;
        }
        $toReturn = array();
        foreach($results as $entry) {
            $occurred = new DateTime($entry['date']);
            $toReturn[] = new logEntry($entry['eventID'], $entry['type'], $entry['message'], $entry['userID'], $occurred);
        }
        return $toReturn;
    }
    public function clearLog() {
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanClearLog')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $success = $database->makeCustomQuery('TRUNCATE TABLE systemLog');
        if($success === false) {
            return false;
        }
        return true;
    }
}
abstract class logEntryType {
    const warning = 'warning';
    const info = 'info';
    const neutral = 'neutral';
    public static function validateType($in) {
        if ((!strcmp($in, logEntryType::info)) and (!strcmp($in, logEntryType::neutral)) and (!strcmp($in, logEntryType::warning))) {
            return false;
        }
        return true;
    }
}