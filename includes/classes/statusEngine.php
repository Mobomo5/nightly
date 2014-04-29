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
 * nodeID         INT   revisorID      INT
 *                      isCurrent       INT
 */

//TODO: supporterCount addressed...
require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

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

    //Constructor Start -- Get database and permissions engine.
    private $db;
    private $permissionObject;

    private function __construct() {
        $this->permissionObject = permissionEngine::getInstance();
        $this->db = database::getInstance();
    }
    //Constructor End

    /*
     * Functions for adding and updatings statuses from the database
     */
    public function addStatus($inPosterID, $inParentStatus, $inSupporterCount, $inNodeID, $inStatus){
        if(!$this->permissionObject->checkPermission("canPostStatus")){
            return;
        }

        //inserts the status into the database
        $this->db->insertData("status", "posterID, parentStatus,supporterCount, nodeID", "$inPosterID, $inParentStatus, $inSupporterCount, $inNodeID");

        //Select query for getting StatusID for next insert
        $results = $this->db->getData("statusID", "status" ,"'posterID' = $inPosterID");
        $statusID = $results[0]['statusID'];

        //insert into the statusRevision table
        $escapedStatus = $this->db->escapeString($inStatus);
        $timestamp = date("Y-m-d H:i:s");
        $this->db->insertData("statusRevision", "status, timePosted, statusID, isCurrent", "$escapedStatus, $timestamp, $statusID, 1");
    }

    public function updateStatus($inStatus, $inStatusID, $inUserID){
        /* In the update function, you are taking in the status, statusID, and the userID of the person updating.
         * Those values just get inserted into the proper column in the statusRevision table.
         */
        if(!$this->permissionObject->checkPermission("canEditOwnStatus") || !$this->permissionObject->checkPermission("canEditOthersStatus")){
            return;
        }

        //UPDATE statusRevision SET isCurrent = 0, revisorID = $inUserID WHERE statusID = $inStatusID;
        $this->db->updateTable("statusRevision","isCurrent = 0, revisorID = $inUserID","statusID = $inStatusID");

        //insert into the statusRevision table
        $escapedStatus = $this->db->escapeString($inStatus);
        $timestamp = date("Y-m-d H:i:s");
        $this->db->insertData("statusRevision", "status, timePosted, statusID, isCurrent", "$escapedStatus, $timestamp, $inStatusID, 1");
    }

    /*
     * Functions for Retrieving Statuses from Database
     */

    public function retrieveStatusByUser($inUserID){
        //get statuses by user ID.
        $statusArray = array();

        $results = $this->db->getData("*",
            "status INNER JOIN statusRevision ON status.statusID = statusRevision.statusID",
            "'posterID' = $inUserID"); //<----


        foreach($results as $row){
            $statusArray[] += new status($row['statusID'], $row['status'], $row['posterID'], $row['nodeID']);
        }

        return $statusArray;
    }

    public function retrieveStatusByNode($inNodeID){
        //get statuses based off of NodeID
        $statusArray = array();

        $results = $this->db->getData("*",
            "status INNER JOIN statusRevision ON status.statusID = statusRevision.statusID",
            "'nodeID' = $inNodeID");

        foreach($results as $row){
            $statusArray[] += new status($row['statusID'], $row['status'], $row['posterID'], $row['nodeID']);
        }

        return $statusArray;
    }
}