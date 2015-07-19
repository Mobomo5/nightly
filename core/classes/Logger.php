<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 07/01/14
 * Time: 12:45 PM
 */
class Logger {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing.
    }
    public function logIt(LogEntry $entry) {
        $type = $entry->getType();
        $message = $entry->getMessage();
        $userID = $entry->getUserID();
        $db = Database::getInstance();
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
        if (!PermissionEngine::getInstance()->currentUserCanDo('userCanViewLog')) {
            return false;
        }
        $db = Database::getInstance();
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
            $toReturn[] = new LogEntry($entry['eventID'], $entry['type'], $entry['message'], $entry['userID'], $occurred);
        }
        return $toReturn;
    }
    public function clearLog() {
        if (!PermissionEngine::getInstance()->currentUserCanDo('userCanClearLog')) {
            return false;
        }
        $database = Database::getInstance();
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