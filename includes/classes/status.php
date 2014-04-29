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
    private $nodeID; //what node the status was posted too
    private $childStatus; //Array for child statuses
    private $votes; //counter for how many people like the posting
    private $voterArray; //array of voters to prevent duplicated vote spamming

    public function __construct($inStatusID, $inString, $inUserID, $inNodeID) {
        $this->statusID = $inStatusID;
        $this->statusMsg = $inString;
        $this->posterID = $inUserID;
        $this->nodeID = $inNodeID;
        $this->childStatus = array();
        $this->votes = 0;
        $this->voterArray = array();
    }

    public function addChildStatusTo(status $inChildStatus){
        $this->childStatus[] += $inChildStatus;
    }

    public function removeChildStatusTo(status $inChildStatus){
        //Look for the child status in the array and remove it.
        foreach($this->childStatus as $child){
            if($child->getStatusID() == $inChildStatus->getStatusID()){
                $this->childStatus[] = null;
            }
        }
    }

    /* Check the Array of voters to see if this user has already voted
     * on this status. If he has, do nothing, and if he has not, then
     * allow the vote and add his ID to the array of voters
     */
    public function upvote($inUserID){
        $alreadyVoted = false;
        foreach($this->voterArray as $user){
            if($user == $inUserID){
                $alreadyVoted = true;
            }
        }

        if(! $alreadyVoted){
            $this->votes++;
            $this->voterArray[] += $inUserID;
        }
    }

    public function downvote($inUserID){
        $alreadyVoted = false;
        foreach($this->voterArray as $user){
            if($user == $inUserID){
                $alreadyVoted = true;
            }
        }

        if($alreadyVoted){
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

    public function getStatus(){
        return $this->statusMsg;
    }

    public function getUpvotes(){
        return $this->votes;
    }

    public function getNodeID(){
        return $this->nodeID;
    }
}