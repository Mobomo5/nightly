<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 03/06/14
 * Time: 9:17 PM
 */
class nodeFieldRevision {
    private $id;
    private $content;
    private $timePosted;
    private $authorID;
    private $nodeID;
    private $fieldType;
    private $isCurrent;
    public function __construct($inID, $inContent, DateTime $inTimePosted, $inAuthorID, $inNodeID, nodeFieldType $inFieldType, $inIsCurrent = true) {
        if(! is_numeric($inID)) {
            return;
        }
        if(! is_numeric($inAuthorID)) {
            return;
        }
        if(! is_numeric($inNodeID)) {
            return;
        }
        if(! is_bool($inIsCurrent)) {
            return;
        }
        $this->id = $inID;
        $this->content = $inContent;
        $this->timePosted = $inTimePosted;
        $this->authorID = $inAuthorID;
        $this->nodeID = $inNodeID;
        $this->fieldType = $inFieldType;
        $this->isCurrent = $inIsCurrent;
    }
    public function getID() {
        return $this->id;
    }
    public function getContent() {
        return $this->content;
    }
    public function setContent($inContent) {
        $this->content = $inContent;
    }
    public function getTimePosted() {
        return $this->timePosted;
    }
    public function getAuthorID() {
        return $this->authorID;
    }
    public function getNodeID() {
        return $this->nodeID;
    }
    public function getFieldType() {
        return $this->fieldType;
    }
    public function isCurrent() {
        return $this->isCurrent;
    }
    public function setIsCurrent($inIsCurrent = false) {
        if(! is_bool($inIsCurrent)) {
            return;
        }
        $this->isCurrent = $inIsCurrent;
    }
}