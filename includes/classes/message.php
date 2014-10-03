<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 29/05/14
 * Time: 1:58 PM
 */
class message {
    private $id;
    private $trashed;
    private $isRead;
    private $statusID;
    private $senderID;
    private $nodeID;
    public function __construct($inID, $inTrashed, $inIsRead, $inStatusID, $inSenderID, $inNodeID) {
        if (!is_numeric($inID)) {
            return;
        }
        if (!is_bool($inTrashed)) {
            return;
        }
        if (!is_bool($inIsRead)) {
            return;
        }
        if (!is_numeric($inStatusID)) {
            return;
        }
        if (!is_numeric($inSenderID)) {
            return;
        }
        if (!is_numeric($inNodeID)) {
            return;
        }
        $this->id = $inID;
        $this->trashed = $inTrashed;
        $this->isRead = $inIsRead;
        $this->statusID = $inStatusID;
        $this->senderID = $inSenderID;
        $this->nodeID = $inNodeID;
    }
    public function getID() {
        return $this->id;
    }
    public function isTrashed() {
        return $this->trashed;
    }
    public function setTrashed($inBool) {
        if ($inBool == true || $inBool = 1) {
            $this->trashed = true;
        } else {
            $this->trashed = false;
        }
    }
    public function isRead() {
        return $this->isRead;
    }
    public function setIsRead($inBool) {
        if ($inBool == true || $inBool = 1) {
            $this->isRead = true;
        } else {
            $this->isRead = false;
        }
    }
    public function getStatusID() {
        return $this->statusID;
    }
    public function getSenderID() {
        return $this->senderID;
    }
    public function getNodeID() {
        return $this->nodeID;
    }
} 