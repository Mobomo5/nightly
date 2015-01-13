<?php
class status {
    private $id;
    private $posterID;
    private $parentStatusID;
    private $supporterCount;
    private $nodeID;
    private $revision;
    public function __construct($inID, $inPosterID, $inParentStatusID, $inSupporterCount, $inNodeID, statusRevision $inStatusRevision) {
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
        $this->id = $inID;
        $this->posterID = $inPosterID;
        $this->parentStatusID = $inParentStatusID;
        $this->supporterCount = $inSupporterCount;
        $this->nodeID = $inNodeID;
        $this->revision = $inStatusRevision;
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
    public function getRevision() {
        return $this->revision;
    }
}