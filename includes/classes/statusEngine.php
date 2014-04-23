<?php
/**
 * User: Keegan Bailey
 * Date: 23/04/14
 * Time: 12:23 PM
 *
 * Handles getting status from and to the database
 * Handles editing statuses
 *
 * Status DB            StatusRevision DB
 * statusID       INT   revisionID      INT
 * posterID       INT   status          VARCHAR(1000)
 * parentStatus   INT   timePosted      TIMESTAMP
 * supporterCount INT   statusID        INT
 * nodeID         INT   revisionID      INT
 *                      isCurrent       INT
 */
require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);

class statusEngine {
    /* Checking to see if the instance variable is holding onto to status engine object
     * and if it's not create one.
     */
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new statusEngine();
        }

        return self::$instance;
    }

    private function __construct() {

    }

    public function addStatusToDatabase($inPosterID, $inParentStatus, $inSupporterCount, $inNodeID, $inStatus){
        //inserts the status into the database
        $db = database::getInstance();
        $db->insert("status", "posterID, parentStatus,supporterCount, nodeID", "$inPosterID, $inParentStatus, $inSupporterCount, $inNodeID");



        $escapedStatus = $db->escapeString($inStatus);
        $timestamp = date("Y-m-d H:i:s");
        $db->insert("statusRevision", "status, timePosted, statusID, isCurrent", "$escapedStatus, $timestamp, 1");

    }

    public function retrieveStatusFromDatabase(){
        //create status objects
    }
}