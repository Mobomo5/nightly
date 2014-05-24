<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 07/01/14
 * Time: 12:45 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(LOG_ENTRY_OBJECT_FILE);

class logger {
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new logger();
        }

        return self::$instance;
    }

    private function __construct() {
    }

    public function logIt(logEntry $entry) {
        $type = $entry->getType();
        $message = $entry->getMessage();
        $userID = $entry->getUserID();

        $db = database::getInstance();
        $message = $db->escapeString($message);
        $results = $db->insertData('systemLog', 'message, type, userID', '\'' . $message . '\', \'' . $type . '\', \'' . $userID . '\'');
        if (!$results) {
            return false;
        }
        // get EventID and return it
        $results = $db->getData('eventID', 'systemLog', 'message = \'' . $message . '\' AND type = \'' . $type . '\' AND userID = \'' . $userID . '\'');
        if (!$results) {

            return false;
        }
        return $results[0]['eventID'];

    }

    public function getLog($level = 'all') {
        if (!permissionEngine::getInstance()->checkPermissionByName('userCanViewLog')) {
            return false;
        }
        $db = database::getInstance();
        if (strcmp($level, 'info')) {
            $results = $db->getData('*', 'systemLog', 'type = \'info\'');
        } elseif (strcmp($level, 'warning')) {
            $results = $db->getData('*', 'systemLog', 'type = \'warning\'');
        } elseif (strcmp($level, 'neutral')) {
            $results = $db->getData('*', 'systemLog', 'type = \'neutral\'');
        } else {
            $results = $db->getData('*', 'systemLog');
        }
        return $results;
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