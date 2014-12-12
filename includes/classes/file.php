<?php
require_once(MIME_TYPE_ARRAYS_OBJECT_FILE);
class file {
    private $id;
    private $uploaded;
    private $title;
    private $mimeType;
    private $size;
    private $location;
    private $nodeID;
    private $uploader;
    private $folderID;
    public function __construct($inID, DateTime $inUploadedDate, $inTitle, $inMimeType, $inSize, $inLocation, $inNodeID, $inUploader, $inFolderID) {
        if(! is_numeric($inID)) {
            return;
        }
        if($inID < 0) {
            return;
        }
        $inTitle = strip_tags($inTitle);
        if(! mimeType::checkIfKnownMimeType($inMimeType)) {
            return;
        }
        if(! is_numeric($inSize)) {
            return;
        }
        if($inSize < 0) {
            return;
        }
        if(! is_file($inLocation)) {
            return;
        }
        if(! is_numeric($inNodeID)) {
            return;
        }
        if($inNodeID < 0) {
            return;
        }
        if(! is_numeric($inUploader)) {
            return;
        }
        if($inUploader < 0) {
            return;
        }
        if(! is_numeric($inFolderID)){
            return;
        }
        if($inFolderID < 0) {
            return;
        }
        $this->id = $inID;
        $this->uploaded = $inUploadedDate;
        $this->title = $inTitle;
        $this->mimeType = $inMimeType;
        $this->size = $inSize;
        $this->location = $inLocation;
        $this->nodeID = $inNodeID;
        $this->uploader = $inUploader;
        $this->folderID = $inFolderID;
    }
    public function getID() {
        return $this->id;
    }
    public function getUploadedDate() {
        return $this->uploaded;
    }
    public function setUploadedDate(DateTime $inUploadedDate) {
        $this->uploaded = $inUploadedDate;
    }
    public function getTitle() {
        return $this->title;
    }
    public function setTitle($inTitle) {
        $this->title = strip_tags($inTitle);
    }
    public function getMimeType() {
        return $this->mimeType;
    }
    public function setMimeType($inMimeType) {
        if(! mimeType::checkIfKnownMimeType($inMimeType)) {
            return;
        }
        $this->mimeType = $inMimeType;
    }
    public function getSize() {
        return $this->size;
    }
    public function setSize($inSize) {
        if(! is_numeric($inSize)) {
            return;
        }
        if($inSize < 0) {
            return;
        }
        $this->size = $inSize;
    }
    public function getLocation() {
        return $this->location;
    }
    public function setLocation($locationOnServer) {
        if(! is_file($locationOnServer)) {
            return;
        }
        $this->location = $locationOnServer;
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
    public function getUploaderID() {
        return $this->uploader;
    }
    public function setUploaderID($inUploaderID) {
        if(! is_numeric($inUploaderID)) {
            return;
        }
        if($inUploaderID < 0) {
            return;
        }
        $this->uploader = $inUploaderID;
    }
    public function getFolderID() {
        return $this->folderID;
    }
    public function setFolderID($inFolderID) {
        if(! is_numeric($inFolderID)) {
            return;
        }
        if($inFolderID < 0) {
            return;
        }
        $this->folderID = $inFolderID;
    }
}