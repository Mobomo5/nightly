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

        //Select query for getting StatusID for next insert
        $results = $db->select("statusID", "status" ,"posterID` = $inPosterID");
        $statusID = mysql_fetch_row($results);

        //insert into the statusRevision table
        $escapedStatus = $db->escapeString($inStatus);
        $timestamp = date("Y-m-d H:i:s");
        $db->insert("statusRevision", "status, timePosted, statusID, isCurrent", "$escapedStatus, $timestamp, $statusID[0], 1");
    }

    public function retrieveStatusFromDatabaseByUser($inUserID){
        //Create status objects
        $db = database::getInstance();
        $statusArray = array();

        //TODO: Make Query for getting all the information to create the status object
        $results = $db->select("","",""); //<----

        while($row = mysqli_fetch_array($results)) {
            $statusArray[] += new status($row['statusID'], $row['status'], $row['posterID'], $row['nodeID']);
        }

        return $statusArray;
    }

    public function retrieveStatusFromDatabaseByNode($inNodeID){
        //Create status objects
        $db = database::getInstance();
        $statusArray = array();

        //TODO: Make Query for getting all the information to create the status object from status and statusRevision DB
        $results = $db->select("","",""); //<----

        while($row = mysqli_fetch_array($results)) {
            $statusArray[] += new status($row['statusID'], $row['status'], $row['posterID'], $row['nodeID']);
        }

        return $statusArray;
    }
}