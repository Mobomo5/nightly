<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 4:07 PM
 */
class folder {
    private $id;
    private $title;
    private $created;
    private $ownerID;
    private $parentFolder;
    private $childFilesAndFolders;
    private $TITLEMAXSIZE = 50;

    public function __construct($inID, $inTitle, $inCreated, $inOwnerID, $inParentFolder, $inChildFilesAndFolders) {
        //validate

        if (strlen($inTitle) > $this->TITLEMAXSIZE) {
            return false;
        }

        $this->id = $inID;
        $this->title = $inTitle;
        $this->created = $inCreated;
        $this->ownerID = $inOwnerID;
        $this->parentFolder = $inParentFolder;
        $this->childFilesAndFolders = $inChildFilesAndFolders;


    }

    /**
     * @return mixed
     */
    public function getChildFilesAndFolders() {
        return $this->childFilesAndFolders;
    }

    /**
     * @return mixed
     */
    public function getCreated() {
        return $this->created;
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
    public function getOwnerID() {
        return $this->ownerID;
    }

    /**
     * @return mixed
     */
    public function getParentFolder() {
        return $this->parentFolder;
    }

    /**
     * @return mixed
     */
    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {
        if (strlen($inTitle) > $this->TITLEMAXSIZE) {
            return false;
        }
    }

} 