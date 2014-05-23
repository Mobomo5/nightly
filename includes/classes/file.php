<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 3:54 PM
 */
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
    private $TITLEMAXLENGTH = 50;

    public function __construct($inID, $inUploaded, $inTitle, $inMimeType, $inSize, $inLocation, $inNodeID, $inUploader, $inFolderID) {

        // validate
        if (strlen($inTitle) > $this->TITLEMAXLENGTH) {
            return false;
        }
        //@todo: better validation
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