<?php
/**
 * User: Keegan Bailey
 * Date: 23/04/14
 * Time: 12:23 PM
 *
 * Handles getting status from and to the database
 * Handles editing statuses
 *
 * Status DB StatusRevision DB
 * statusID INT revisionID INT
 * posterID INT status VARCHAR(1000)
 * parentStatus INT timePosted TIMESTAMP
 * supporterCount INT statusID INT
 * nodeID INT revisionID INT
 * isCurrent INT
 */
require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(STATUS_OBJECT_FILE);

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

    private $permissionObject;
    private $db;

    private function __construct() {
        $this->permissionObject = permissionEngine::getInstance();
        $this->db = database::getInstance();
    }

    public function addStatusToDatabase($inPosterID, $inParentStatus, $inSupporterCount, $inNodeID, $inStatus) {
        //inserts the status into the database
        $this->db->insertData("status", "posterID, parentStatus,supporterCount, nodeID", "$inPosterID, $inParentStatus, $inSupporterCount, $inNodeID");

        //Select query for getting StatusID for next insert
        $results = $this->db->getData("statusID", "status", "'posterID' = $inPosterID");
        $statusID = $results[0]['statusID'];

        //insert into the statusRevision table
        $escapedStatus = $this->db->escapeString($inStatus);
        $timestamp = date("Y-m-d H:i:s");
        $this->db->insertData("statusRevision", "status, timePosted, statusID, isCurrent", "$escapedStatus, $timestamp, $statusID, 1");
    }

    //Get statuses from Database and create an array of status objects
    public function retrieveStatusFromDatabaseByUser($inUserID) {
        //Create status objects
        $statusArray = array();

        $results = $this->db->getData("*",
            "status INNER JOIN statusRevision ON status.statusID = statusRevision.statusID",
            "'posterID' = $inUserID"); //<----

        foreach ($results as $row) {
            $statusArray[] += new status($row['statusID'], $row['status'], $row['posterID'], $row['nodeID']);
        }

        return $statusArray;
    }

    //Get statuses from Database and create an array of status objects
    public function retrieveStatusFromDatabaseByNode($inNodeID) {
        //Create status objects
        $statusArray = array();

        $results = $this->db->getData("*",
            "status INNER JOIN statusRevision ON status.statusID = statusRevision.statusID",
            "nodeID = $inNodeID");

        if (!$results) {
            return false;
        }

        foreach ($results as $row) {
            $statusArray[] = new status($row['statusID'], $row['status'], $row['posterID'], $row['nodeID']);
        }

        return $statusArray;
    }

    //Editing status's
    public function editStatusInDatabaseByID($inStatusID, $inUpdatedStatus) {
        $results = $this->db->getData("*",
            "status INNER JOIN statusRevision ON status.statusID = statusRevision.statusID",
            "'statusID' = $inStatusID");

        //update statusRevision table
        $this->db->updateTable("statusRevision", "isCurrent = 0", "statusID = $inStatusID");

    }
}