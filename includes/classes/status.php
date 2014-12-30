<?php
class status {
    private $id;
    private $posterID;
    private $parentStatusID;
    private $supporterCount;
    private $nodeID;
    private $message;
    public function __construct($inID, $inPosterID, $inParentStatusID, $inSupporterCount, $inNodeID, $inMessage) {
        if(! is_numeric($inID)) {
            return;
        }
        if($inID < 0) {
            return;
        }
        if(! is_numeric($inPosterID)) {
            return;
        }
        if($inPosterID < 0) {
            return;
        }
        if(! is_numeric($inParentStatusID)) {
            return;
        }
        if($inParentStatusID < 0) {
            return;
        }
        if(! is_numeric($inSupporterCount)) {
            return;
        }
        if($inSupporterCount < 0) {
            return;
        }
        if(! is_numeric($inNodeID)) {
            return;
        }
        if($inNodeID < 0) {
            return;
        }
        $inMessage = strip_tags($inMessage);
        $this->id = $inID;
        $this->posterID = $inPosterID;
        $this->parentStatusID = $inParentStatusID;
        $this->supporterCount = $inSupporterCount;
        $this->nodeID = $inNodeID;
        $this->message = $inMessage;
    }
    public function getID() {
        return $this->id;
    }
    public function getPosterID() {
        return $this->posterID;
    }
    public function  getParentStatusID() {
        return $this->parentStatusID;
    }
    public function setParentStatusID($inNewParentStatusID) {
        if(! is_numeric($inNewParentStatusID)) {
            return;
        }
        if($inNewParentStatusID < 0) {
            return;
        }
        $this->parentStatusID = $inNewParentStatusID;
    }
    public function getSupporterCount() {
        return $this->supporterCount;
    }
    public function getNodeID() {
        return $this->nodeID;
    }
    public function setNodeID($inNodeID) {
        if(! is_numeric($inNodeID)) {
            return;
        }
        if($inNodeID < 0) {
            return;
        }
        $this->nodeID = $inNodeID;
    }
    public function getMessage() {
        return $this->message;
    }
    public function setMessage($inMessage) {
        $this->message = strip_tags($inMessage);
    }
}