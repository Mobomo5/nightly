<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 05/01/2015
 * Time: 8:09 PM
 */

class StatusRevision {
    private $id;
    private $message;
    private $timePosted;
    private $statusID;
    private $revisorID;
    private $isCurrent;
    public function __construct($inID, $inMessage, DateTime $inTimePosted, $inStatusID, $inRevisorID, $inIsCurrent = false) {
        if(! is_numeric($inID)) {
            return;
        }
        if($inID < 0) {
            return;
        }
        if(! is_numeric($inStatusID)) {
            return;
        }
        if($inStatusID < 0) {
            return;
        }
        if(! is_numeric($inRevisorID)) {
            return;
        }
        if($inRevisorID < 0) {
            return;
        }
        if(! is_bool($inIsCurrent)) {
            return;
        }
        $inMessage = strip_tags($inMessage);
        $this->id =$inID;
        $this->message = $inMessage;
        $this->timePosted = $inTimePosted;
        $this->statusID = $inStatusID;
        $this->revisorID = $inRevisorID;
        $this->isCurrent = $inIsCurrent;
    }
    public function getID() {
        return $this->id;
    }
    public function getMessage() {
        return $this->message;
    }
    public function getTimePosted() {
        return $this->timePosted;
    }
    public function getStatusID() {
        return $this->statusID;
    }
    public function getRevisorID() {
        return $this->revisorID;
    }
    public function getIsCurrent() {
        return $this->isCurrent;
    }
    public function setIsCurrent($inIsCurrent = false) {
        if(! is_bool($inIsCurrent)) {
            return;
        }
        if($inIsCurrent === false) {
            $this->isCurrent = false;
            return;
        }
        $this->isCurrent = true;
    }
}