<?php
require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(STATUS_OBJECT_FILE);
require_once(STATUS_REVISION_OBJECT_FILE);
class statusEngine {
    private static $instance;
    private $foundStatusRevisions;
    private $foundStatuses;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new statusEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundStatusRevisions = array();
        $this->foundStatuses = array();
    }
    public function getStatus($inID) {
        if(! is_numeric($inID)) {
            return false;
        }
        if(isset($this->foundStatuses[$inID])) {
            return $this->foundStatuses[$inID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inID = $database->escapeString($inID);
        $results = $database->getData('statusID, posterID, parentStatus, supporterCount, nodeID', 'status', "statusID={$inID}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $statusRevision = $this->getCurrentStatusRevisionForStatus($inID);
        if($statusRevision == false) {
            return false;
        }
        $statusID = $results[0]['statusID'];
        $posterID = $results[0]['postedID'];
        $parentStatus = $results[0]['parentStatus'];
        $supporterCount = $results[0]['supporterCount'];
        $nodeID = $results[0]['nodeID'];
        $toReturn = new status($statusID, $posterID, $parentStatus, $supporterCount, $nodeID, $statusRevision);
        $this->foundStatuses[$inID] = $toReturn;
        return $toReturn;
    }
    public function getStatusesOnNode($inNodeID) {
        if(! $inNodeID) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inNodeID = $database->escapeString($inNodeID);
        $results = $database->getData('statusID, posterID, parentStatus, supporterCount, nodeID', 'status', "nodeID={$inNodeID}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        $toReturn = array();
        foreach($results as $result) {
            if(isset($this->foundStatuses[$result['statusID']])) {
                $toReturn[] = $this->foundStatuses[$result['statusID']];
                continue;
            }
            $statusRevision = $this->getCurrentStatusRevisionForStatus($result['statusID']);
            if($statusRevision == false) {
                continue;
            }
            $statusID = $result['statusID'];
            $posterID = $result['postedID'];
            $parentStatus = $result['parentStatus'];
            $supporterCount = $result['supporterCount'];
            $nodeID = $result['nodeID'];
            $toAdd = new status($statusID, $posterID, $parentStatus, $supporterCount, $nodeID, $statusRevision);
            $this->foundStatuses[$result['statusID']];
            $toReturn[] = $toAdd;
        }
        return $toReturn;
    }
    public function getStatusesByUser($inUserID) {
        if(! $inUserID) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inUserID = $database->escapeString($inUserID);
        $results = $database->getData('statusID, posterID, parentStatus, supporterCount, nodeID', 'status', "posterID={$inUserID}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        $toReturn = array();
        foreach($results as $result) {
            if(isset($this->foundStatuses[$result['statusID']])) {
                $toReturn[] = $this->foundStatuses[$result['statusID']];
                continue;
            }
            $statusRevision = $this->getCurrentStatusRevisionForStatus($result['statusID']);
            if($statusRevision == false) {
                continue;
            }
            $statusID = $result['statusID'];
            $posterID = $result['postedID'];
            $parentStatus = $result['parentStatus'];
            $supporterCount = $result['supporterCount'];
            $nodeID = $result['nodeID'];
            $toAdd = new status($statusID, $posterID, $parentStatus, $supporterCount, $nodeID, $statusRevision);
            $this->foundStatuses[$result['statusID']] = $toAdd;
            $toReturn[] = $toAdd;
        }
        return $toReturn;
    }
    private function getCurrentStatusRevisionForStatus($inStatusID) {
        if(! is_numeric($inStatusID)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inStatusID = $database->escapeString(($inStatusID));
        $results = $database->getData('revisionID', 'statusRevision', "statusID={$inStatusID} AND isCurrent=1");
        if($results == false) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        if($results == null) {
            return false;
        }
        return $this->getStatusRevision($results[0]['revisionID']);
    }
    public function getStatusRevision($revisionID) {
        if(! is_numeric($revisionID)) {
            return false;
        }
        if(isset($this->foundStatusRevisions[$revisionID])) {
            return $this->foundStatusRevisions[$revisionID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $revisionID = $database->escapeString($revisionID);
        $results = $database->getData('revisionID, status, timePosted, statusID, revisorID, isCurrent', 'statusRevision', "revisionID={$revisionID}");
        if($results == false) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        if($results == null) {
            return false;
        }
        $revisionID = $results[0]['revisionID'];
        $message = strip_tags($results[0]['status']);
        $timePosted = new DateTime($results[0]['timePosted']);
        $statusID = $results[0]['statusID'];
        $revisorID = $results[0]['revisorID'];
        if((int) $results[0]['isCurrent'] == 1) {
            $isCurrent = true;
        } else {
            $isCurrent = false;
        }
        $toReturn = new statusRevision($revisionID, $message, $timePosted, $statusID, $revisorID, $isCurrent);
        $this->foundStatusRevisions[$toReturn->getID()] = $toReturn;
        return $toReturn;
    }
    public function getStatusRevisionsForStatus($statusID) {
        if(! is_numeric($statusID)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $statusID = $database->escapeString($statusID);
        $results = $database->getData('revisionID, status, timePosted, statusID, revisorID, isCurrent', 'statusRevision', "statusID={$statusID}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        $toReturn = array();
        foreach($results as $result) {
            if(isset($this->foundStatusRevisions[$result['revisionID']])) {
                $toReturn[] = $this->foundStatusRevisions[$result['revisionID']];
                continue;
            }
            $revisionID = $result['revisionID'];
            $status = $result['status'];
            $timePosted = new DateTime($result['timePosted']);
            $statusID = $result['statusID'];
            $revisorID = $result['revisorID'];
            if((int) $result['isCurrent'] == 1) {
                $isCurrent = true;
            } else {
                $isCurrent = false;
            }
            $toAdd = new statusRevision($revisionID, $status, $timePosted, $statusID, $revisorID, $isCurrent);
            $this->foundStatusRevisions[$toAdd->getID()] = $toAdd;
            $toReturn[] = $toAdd;
        }
        return $toReturn;
    }
}