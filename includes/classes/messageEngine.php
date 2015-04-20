<?php
require_once(DATABASE_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(MESSAGE_OBJECT_FILE);
class messageEngine {
    private static $instance;
    private $foundMessages;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new messageEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundMessages = array();
    }
    public function getMessage($inID) {
        if (!is_numeric($inID)) {
            return;
        }
        $permissionEngines = permissionEngine::getInstance();
        if(! $permissionEngines->currentUserCanDo("canViewMessages")) {
            return false;
        }
        if(isset($this->foundMessages[$inID])) {
            return $this->foundMessages[$inID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $results = $database->getData("messageID, trashed, isRead, statusID, senderID, nodeID", "message", "messageID = {$inID}");
        if($results === false) {
            return false;
        }
        if($results === null) {
            return false;
        }
        if(count($results) < 1) {
            return false;
        }
        if((int)$results[0]['trashed'] === 1) {
            $trashed = true;
        } else {
            $trashed = false;
        }
        if((int)$results[0]['isRead'] === 1) {
            $isRead = true;
        } else {
            $isRead = false;
        }
        $message = new message($results[0]['messageID'], $trashed, $isRead, $results[0]['statusID'], $results[0]['senderID'], $results[0]['nodeID']);
        $this->foundMessages[$message->getID()] = $message;
        return $message;
    }
    public function addMessage(message $toAdd, $recipientID) {
        if(! is_numeric($recipientID)){
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canSendMessages")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if($toAdd->isRead()) {
            $isRead = 1;
        } else {
            $isRead = 0;
        }
        if($toAdd->isTrashed()) {
            $isTrashed = 1;
        } else {
            $isTrashed = 0;
        }
        $isRead = $database->escapeString($isRead);
        $isTrashed = $database->escapeString($isTrashed);
        $statusID = $database->escapeString($toAdd->getStatusID());
        $senderID = $database->escapeString(currentUser::getUserSession()->getUserID());
        $nodeID = $database->escapeString($toAdd->getNodeID());
        $recipientID = $database->escapeString($recipientID);
        $result = $database->insertData("message", "trashed, isRead, statusID, senderID, nodeID", "{$isTrashed}, {$isRead}, {$statusID}, {$senderID}, {$nodeID}");
        if($result === false) {
            return false;
        }
        $messageID = $database->getLastInsertID();
        $messageID = $database->escapeString($messageID);
        $result = $database->insertData("messageRecipient", "messageID, recipientID", "{$messageID}, {$recipientID}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function saveMessage(message $toSave) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canSaveMessages")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if($toSave->isRead()) {
            $isRead = 1;
        } else {
            $isRead = 0;
        }
        if($toSave->isTrashed()) {
            $isTrashed = 1;
        } else {
            $isTrashed = 0;
        }
        $isRead = $database->escapeString($isRead);
        $isTrashed = $database->escapeString($isTrashed);
        $messageID = $database->escapeString($toSave->getID());
        $result = $database->updateTable("message", "isRead={$isRead}, trashed={$isTrashed}", "messageID={$messageID}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function deleteMessage(message $toDelete) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canDeleteMessages")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $messageID = $database->escapeString($toDelete->getID());
        $result = $database->removeData("messageRecipient","messageID={$messageID}");
        if($result === false) {
            return false;
        }
        $statusEngine = statusEngine::getInstance();
        $status = $statusEngine->getStatus($toDelete->getStatusID());
        if($status === false) {
            return false;
        }
        $result = $statusEngine->removeStatus($status);
        if($result === false) {
            return false;
        }
        $result = $database->removeData("message", "messageID={$messageID}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function removeAllTrashedMessages() {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("canDeleteAllTrashedMessages")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $results = $database->getData("messageID", "message", "trashed=0");
        if($results === false) {
            return false;
        }
        if($results === null) {
            return false;
        }
        if(count($results) < 1) {
            return false;
        }
        foreach($results as $messageID) {
            $messageToRemove = $this->getMessage((int) $messageID);
            if($messageToRemove === false) {
                continue;
            }
            $this->deleteMessage($messageToRemove);
        }
        return true;
    }
}