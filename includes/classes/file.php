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
    public function __construct($inID, DateTime $inUploaded, $inTitle, $inMimeType, $inSize, $inLocation, $inNodeID, $inUploader, $inFolderID) {
        if(! is_numeric($inID)) {
            return;
        }
        $inTitle = strip_tags($inTitle);
        if(! mimeType::checkIfKnownMimeType($inMimeType)) {
            return;
        }
        if(! is_numeric($inSize)) {
            return;
        }
        if(! is_file($inLocation)) {
            return;
        }
        if(! is_numeric($inNodeID)) {
            return;
        }
        if(! is_numeric($inUploader)) {
            return;
        }
        if(! is_numeric($inFolderID)){
            return;
        }
        $this->id = $inID;
        $this->uploaded = $inUploaded;
        $this->title = $inTitle;
        $this->mimeType = $inMimeType;
        $this->size = $inSize;
        $this->location = $inLocation;
        $this->nodeID = $inNodeID;
        $this->uploader = $inUploader;
        $this->folderID = $inFolderID;
    }
    /**
     * @return mixed
     */
    public function getFolderID() {
        return $this->folderID;
    }
    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }
    /**
     * @return mixed
     */
    public function getLocation() {
        return $this->location;
    }
    /**
     * @return mixed
     */
    public function getMimeType() {
        return $this->mimeType;
    }
    /**
     * @return mixed
     */
    public function getNodeID() {
        return $this->nodeID;
    }
    /**
     * @return mixed
     */
    public function getSize() {
        return $this->size;
    }
    /**
     * @return mixed
     */
    public function getTitle() {
        return $this->title;
    }
    public function setTitle($inTitle) {
        if (strlen($inTitle) > $this->TITLEMAXLENGTH) {
            return false;
        }
        if (empty($inTitle)) {
            return false;
        }
        $this->title = $inTitle;
    }
    /**
     * @return mixed
     */
    public function getUploaded() {
        return $this->uploaded;
    }
    /**
     * @return mixed
     */
    public function getUploader() {
        return $this->uploader;
    }
}