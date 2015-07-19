<?php
class Folder {
    private $id;
    private $title;
    private $created;
    private $ownerID;
    private $parentFolder;
    private $childFiles;
    private $childFolders;
    public function __construct($inID, $inTitle, DateTime $inDateCreated, $inOwnerID, $inParentFolderID, array $inChildFiles = array(), array $inChildFolders = array()) {
        if(! is_numeric($inID)) {
            return;
        }
        $inTitle = strip_tags($inTitle);
        if(! is_numeric($inOwnerID)) {
            return;
        }
        if(! is_numeric($inParentFolderID)) {
            return;
        }
        $this->id = $inID;
        $this->title = $inTitle;
        $this->created = $inDateCreated;
        $this->ownerID = $inOwnerID;
        $this->parentFolder = $inParentFolderID;
        $this->childFiles = array();
        $this->childFolders = array();
        foreach($inChildFiles as $childFile) {
            if(! is_object($childFile)) {
                continue;
            }
            $class = get_class($childFile);
            if($class != 'file') {
                continue;
            }
            $this->childFiles[] = $childFile;
        }
        foreach($inChildFolders as $childFolder) {
            if(! is_object($childFolder)) {
                continue;
            }
            $class = get_class($childFolder);
            if($class != 'folder') {
                continue;
            }
            $this->childFolders[] = $childFolder;
        }
    }
    public function getID() {
        return $this->id;
    }
    public function getTitle() {
        return $this->title;
    }
    public function setTitle($inTitle) {
        $this->title = strip_tags($inTitle);
    }
    public function getDateCreated() {
        return $this->created;
    }
    public function setDateCreated(DateTime $inDateCreated) {
        $this->created = $inDateCreated;
    }
    public function getOwnerID() {
        return $this->ownerID;
    }
    public function setOwnerID($inOwnerID) {
        if(! is_numeric($inOwnerID)) {
            return;
        }
        $this->ownerID = $inOwnerID;
    }
    public function getParentFolderID() {
        return $this->parentFolder;
    }
    public function setParentFolderID($inParentFolderID) {
        if(! is_numeric($inParentFolderID)) {
            return;
        }
        $this->parentFolder = $inParentFolderID;
    }
    public function getChildFolders() {
        return $this->childFolders;
    }
    public function getChildFiles() {
        return $this->childFiles;
    }
}