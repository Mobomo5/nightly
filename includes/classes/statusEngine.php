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
        try {
            //inserts the status into the database
            $this->db->insertData("status", "posterID, parentStatus,supporterCount, nodeID", "'$inPosterID', '$inParentStatus', '$inSupporterCount', '$inNodeID'");
            //Select query for getting StatusID for next insert
            $results = $this->db->getData("statusID", "status", "posterID = '$inPosterID'");
            $statusID = $results[0]['statusID'];
            //insert into the statusRevision table
            $escapedStatus = $this->db->escapeString($inStatus);
            $timestamp = date("Y-m-d H:i:s");
            $this->db->insertData("statusRevision", "status, timePosted, statusID, isCurrent", "'{$escapedStatus}', '{$timestamp}', '{$statusID}', '1'");
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }
    //Get statuses from Database and create an array of status objects
    public function retrieveStatusFromDatabaseByUser($inUserID) {
        //Create status objects
        $statusArray = array();
        try {
            $results = $this->db->getData("*",
                "status INNER JOIN statusRevision ON status.statusID = statusRevision.statusID",
                "posterID = $inUserID"); //<----
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
        if (!$results) {
            return $statusArray;
        }
        foreach ($results as $row) {
            $statusArray[] = new status($row['statusID'], $results[0]['parentStatus'], $row['supporterCount'], $row['status'], $row['posterID'], $row['nodeID']);
        }
        return $statusArray;
    }
    //Get statuses from Database and create an array of status objects
    public function retrieveStatusFromDatabaseByNode($inNodeID) {
        //Create status objects
        $statusArray = array();
        try {
            $results = $this->db->getData("*",
                "status INNER JOIN statusRevision ON status.statusID = statusRevision.statusID",
                "nodeID = $inNodeID");
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
        foreach ($results as $row) {
            $statusArray[] = new status($row['statusID'], $results[0]['parentStatus'], $row['supporterCount'], $row['status'], $row['posterID'], $row['nodeID']);
        }
        return $statusArray;
    }
    //Editing status's
    public function editStatusInDatabaseByID($inStatusID, $inUpdatedStatus) {
        try {
            $results = $this->db->getData("*",
                "status INNER JOIN statusRevision ON status.statusID = statusRevision.statusID",
                "statusID = $inStatusID");
            if (!$results) {
                return false;
            }
            //update statusRevision table
            $this->db->updateTable("statusRevision", "isCurrent = 0", "statusID = $inStatusID");
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
        //add new status
        $this->addStatusToDatabase(null, $results[0]['parentStatus'], $results[0]['supporterCount'], $results[0]['nodeID'], $inUpdatedStatus);
    }
    public function supportStatus($inStatusID, $inSupporterID) {
        try {
            //grab all supporters based off of the ID
            $results = $this->db->getData("*", "statusSupporter", "statusID = $inStatusID");
            //the count of the array will = supporter count
            $supporterCount = count($results);
            //see if supporter has already voted, and if he has break out
            foreach ($results as $row) {
                if ($row['supporterID'] == $inSupporterID) {
                    return;
                }
            }
            //add them to the table and update the status in the status table with new support count
            $this->db->insertData("statusSupporter", "supporterID, statusID", "$inSupporterID, $inStatusID");
            $this->db->updateTable("status", "supporterCount = $supporterCount", "statusID = $inStatusID");
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }
}