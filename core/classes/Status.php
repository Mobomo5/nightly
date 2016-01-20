<?php
class Status implements ITimelineObject {
    private $id;
    private $posterID;
    private $poster;
    private $parentStatusID;
    private $supporterCount;
    private $revision;
    public function __construct($inID, $inPosterID, $inParentStatusID, $inSupporterCount, StatusRevision $inStatusRevision) {
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
        $this->id = $inID;
        $this->posterID = $inPosterID;
        $this->poster = null;
        $this->parentStatusID = $inParentStatusID;
        $this->supporterCount = $inSupporterCount;
        $this->revision = $inStatusRevision;
        $this->children = array();
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
    public function getRevision() {
        return $this->revision;
    }
    public function getPoster() {
        return $this->poster;
    }
    public function setPoster(User $poster) {
        if($poster->getUserID() != $this->posterID) {
            return;
        }
        $this->poster = $poster;
    }
    public function getDate() {
        return $this->revision->getTimePosted();
    }
    public function getPriority() {
        return 1;
    }
    public function getSubView() {
        return "@statuses/status.twig";
    }
    public function compareTo(ITimelineObject $other) {
        $myDate = $this->getDate();
        $othersDate = $other->getDate();
        if($myDate > $othersDate) {
            return 1;
        }
        if($myDate < $othersDate) {
            return -1;
        }
        $othersPriority = $other->getPriority();
        if(! is_numeric($othersPriority)) {
            return 1;
        }
        return ($this->getPriority() - $othersPriority);
    }
}