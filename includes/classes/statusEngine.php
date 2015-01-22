<?php
require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(STATUS_OBJECT_FILE);
require_once(STATUS_REVISION_OBJECT_FILE);
require_once(MESSAGE_ENGINE_OBJECT_FILE);
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
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canViewStatuses")) {
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
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canViewStatuses")) {
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
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canViewStatuses")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inUserID = $database->escapeString($inUserID);
        $results = $database->getData('statusID, posterID, parentStatus, supporterCount, nodeID', 'status', "posterID={$inUserID} AND parentStatus=0");
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
    public function getCurrentStatusRevisionForStatus($inStatusID) {
        if(! is_numeric($inStatusID)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canViewStatuses")) {
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
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canViewStatuses")) {
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
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canViewStatuses")) {
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
    public function removeStatus(status $toRemove) {
        $id = $toRemove->getID();
        if(! is_numeric(($id))) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canRemoveStatuses")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($id);
        $result = $database->removeData("statusRevision", "statusID={$id}");
        if($result == false) {
            return false;
        }
        $result = $database->removeData("statusSupporter", "statusID={$id}");
        if($result == false) {
            return false;
        }
        $results = $database->getData("messageID", "message", "statusID={$id}");
        if($results == false) {
            return false;
        }
        $messageEngine = messageEngine::getInstance();
        foreach($results as $entry) {
            $message = $messageEngine->getMessage($entry['messageID']);
            if($message == false) {
                return false;
            }
            $result = $messageEngine->deleteMessage($message);
            if($result == false) {
                return false;
            }
        }
        $results = $database->getData("statusID", "status", "parentStatus={$id}");
        if($results == false) {
            return false;
        }
        foreach($results as $entry) {
            $status = $this->getStatus($entry['statusID']);
            if($status == false) {
                return false;
            }
            $result = $this->removeStatus($status);
            if($result == false) {
                return false;
            }
        }
        return true;
    }
    public function removeStatusRevision(statusRevision $toRemove) {
        $id = $toRemove->getID();
        if(! is_numeric($id)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canRemoveStatusRevisions")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($id);
        $result = $database->removeData("statusRevision", "revisionID={$id}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function addStatus(status $toAdd) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canAddStatuses")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $posterID = $database->escapeString($toAdd->getPosterID());
        $parentStatus = $database->escapeString($toAdd->getParentStatusID());
        $supporterCount = 0;
        $nodeID = $database->escapeString($toAdd->getNodeID());
        $result = $database->insertData("status", "posterID, parentStatus, supporterCount, nodeID", "{$posterID}, {$parentStatus}, {$supporterCount}, {$nodeID}");
        if($result == false) {
            return false;
        }
        $statusID = $database->getLastInsertID();
        $statusRevision = $toAdd->getRevision();
        $statusRevision->setIsCurrent(true);
        return $this->addStatusRevisionInternal($statusRevision, $statusID);
    }
    private function addStatusRevisionInternal(statusRevision $statusRevision, $statusID) {
        if(! is_numeric($statusID)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $status = $database->escapeString(strip_tags($statusRevision->getMessage()));
        $timePosted = $database->escapeString($statusRevision->getTimePosted()->format('Y-m-d H:i:s'));
        $statusID = $database->escapeString($statusID);
        $revisorID = $database->escapeString($statusRevision->getRevisorID());
        if($statusRevision->getIsCurrent() == true) {
            $isCurrent = 1;
        } else {
            $isCurrent = 0;
        }
        $result = $database->insertData("statusRevision", "status, timePosted, statusID, revisorID, isCurrent", "'{$status}', '{$timePosted}', {$statusID}, {$revisorID}, {$isCurrent}");
        if($result == false) {
            return false;
        }
        if($statusRevision->getIsCurrent()) {
            return true;
        }
        $result = $database->updateTable("statusRevision", "isCurrent=0", "statusID={$statusID} AND isCurrent=1");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function addStatusRevision(statusRevision $toAdd) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canReviseStatuses")) {
            return false;
        }
        return $this->addStatusRevisionInternal($toAdd, $toAdd->getStatusID());
    }
    public function toggleCurrentUserSupportForStatus(status $toSupport) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canSupportStatuses")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $user = currentUser::getUserSession();
        $userID = $database->escapeString($user->getUserID());
        $statusID = $database->escapeString($toSupport->getID());
        $results = $database->getData("supporterID", "statusSupporter", "supporterID={$userID} AND statusID={$statusID}");
        if($results == false) {
            return false;
        }
        if($results != null) {
            return $this->removeSupport($statusID, $userID);
        }
        return $this->addSupport($statusID, $userID);
    }
    private function addSupport($statusID, $supporterID) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $result = $database->insertData("statusSupporter", "supporterID, statusID", "{$supporterID}, {$statusID}");
        if($result == false) {
            return false;
        }
        $result = $database->updateTable("status", "supporterCount=supporterCount+1", "statusID={$statusID}");
        if($result == false) {
            return false;
        }
        return true;
    }
    private function removeSupport($statusID, $supporterID) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $result = $database->removeData("statusSupporter", "supporterID={$supporterID} AND statusID={$statusID}");
        if($result == false) {
            return false;
        }
        $result = $database->updateTable("status", "supporterCount=supporterCount-1", "statusID={$statusID}");
        if($result == false) {
            return false;
        }
        return true;
    }
}