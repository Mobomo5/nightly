<?php

/**
 * User: Keegan Bailey
 * Date: 23/04/14
 * Time: 11:00 AM
 *
 * Handles the creation of Status objects
 */
class status {
    private $statusID; //ID of the status
    private $statusMsg; //Contents of the status
    private $posterID; //Who the status belongs too
    private $parentID; //
    private $nodeID; //what node the status was posted too
    private $childStatus; //Array for child statuses
    private $votes; //counter for how many people like the posting
    private $voterArray; //array of voters to prevent duplicated vote spamming
    private $posterName;

    public function __construct($inStatusID = null, $inParentStatusID = null, $inVotes = 0, $inString, $inUserID, $inNodeID)
    {

        if (is_numeric($inStatusID) && $inStatusID != null) {
            $this->statusID = $inStatusID;
        }

        if (is_numeric($inParentStatusID) && $inParentStatusID != null) {
            $this->parentID = $inParentStatusID;
        }

        $this->statusMsg = $inString;
        $this->posterID = $inUserID;
        $this->nodeID = $inNodeID;
        $this->childStatus = array();
        $this->votes = $inVotes;
        $this->voterArray = array();
        $this->posterName = userEngine::getInstance()->getUser($inUserID)->getFullName();
    }

    public function addChildStatusTo(status $inChildStatus) {
        $this->childStatus[] += $inChildStatus;
    }

    public function removeChildStatusFrom(status $inChildStatus) {
        //Look for the child status in the array and remove it.
        foreach ($this->childStatus as $child) {
            if ($child->getStatusID() == $inChildStatus->getStatusID()) {
                $child = null;
                unset($child);
            }
        }
    }

    /* Check the Array of voters to see if this user has already voted
     * on this status. If he has, do nothing, and if he has not, then
     * allow the vote and add his ID to the array of voters
     */
    public function upVote($inUserID) {
        $alreadyVoted = false;
        foreach ($this->voterArray as $user) {
            if ($user == $inUserID) {
                $alreadyVoted = true;
            }
        }

        if (!$alreadyVoted) {
            $this->votes++;
            $this->voterArray[] += $inUserID;
        }
    }

    public function downVote($inUserID) {
        $alreadyVoted = false;
        foreach ($this->voterArray as $user) {
            if ($user == $inUserID) {
                $alreadyVoted = true;
            }
        }

        if ($alreadyVoted) {
            $this->votes--;
            $this->voterArray[] -= $inUserID;
        }
    }

    /*
     * Getters, no setters due to object not being allowed to be edited
     */
    public function getStatusID() {
        return $this->statusID;
    }

    public function getStatus() {
        return $this->statusMsg;
    }

    public function getUpVotes() {
        return $this->votes;
    }

    public function getNodeID() {
        return $this->nodeID;
    }

    public function getPosterID()
    {
        return $this->posterID;
    }

    public function getPosterName() {
        return $this->posterName;
    }

    public function getParentStatusID()
    {
        return $this->parentID;
    }

    public function getPosterHref() {
        return new link('users/' . $this->posterID);
    }

    public function getChildStatus() {
        if (empty($this->childStatus)) {
            return false;
        }
        return $this->childStatus;
    }
}