<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 14/11/2015
 * Time: 7:24 PM
 */
class Timeline implements Iterator {
    private $position;
    private $startingNode;
    private $currentNode;
    public function __construct() {
        $this->position = 0;
        $this->startingNode = null;
        $this->currentNode = null;
    }
    public function add(ITimelineObject $object) {
        $nodeToAdd = new TimelineNode($object);
        if($this->startingNode === null) {
            $this->startingNode = $nodeToAdd;
            $this->currentNode = $nodeToAdd;
            return;
        }
        if(! ($this->startingNode instanceof TimelineNode)) {
            return;
        }
        $nodeToCompareAgainst = $this->startingNode;
        $previousNode = null;
        while($nodeToCompareAgainst !== null) {
            if($nodeToCompareAgainst->compareTo($nodeToAdd) > 0) {
                $previousNode = $nodeToCompareAgainst;
                $nodeToCompareAgainst = $nodeToCompareAgainst->getNextNode();
                continue;
            }
            if($nodeToCompareAgainst === $this->startingNode) {
                $nodeToAdd->setNextNode($nodeToCompareAgainst);
                $this->startingNode = $nodeToAdd;
                return;
            }
            $previousNode->setNextNode($nodeToAdd);
            $nodeToAdd->setNextNode($nodeToCompareAgainst);
            return;
        }
        if($previousNode === null) {
            return;
        }
        //Won't make it to this point unless the end of the linked list is found
        $previousNode->setNextNode($nodeToAdd);
    }
    public function remove(ITimelineObject $object) {
        if($this->startingNode === null) {
            return;
        }
        if(! ($this->startingNode instanceof TimelineNode)) {
            return;
        }
        $nodeToRemove = new TimelineNode($object);
        $nodeToCompareAgainst = $this->startingNode;
        $previousNode = null;
        while($nodeToCompareAgainst !== null) {
            if($nodeToCompareAgainst->compareTo($nodeToRemove) !== 0) {
                $previousNode = $nodeToCompareAgainst;
                $nodeToCompareAgainst = $nodeToCompareAgainst->getNextNode();
                continue;
            }
            if($this->startingNode->compareTo($nodeToCompareAgainst) === 0) {
                $this->removeFirstNode();
                return;
            }
            $nextNode = $nodeToCompareAgainst->getNextNode();
            $previousNode->setNextNode($nextNode);
            return;
        }
    }
    private function removeFirstNode() {
        if(! ($this->startingNode instanceof TimelineNode)) {
            $this->startingNode = null;
            return;
        }
        $secondNode = $this->startingNode->getNextNode();
        if(! ($secondNode instanceof TimelineNode)) {
            $this->startingNode = null;
            return;
        }
        $this->startingNode = $secondNode;
    }
    public function current() {
        if(! ($this->currentNode instanceof TimelineNode)) {
            return null;
        }
        return $this->currentNode->getObject();
    }
    public function key() {
        return $this->position;
    }
    public function next() {
        $this->position += 1;
        if(! ($this->currentNode instanceof TimelineNode)) {
            $this->currentNode = null;
            return;
        }
        $this->currentNode = $this->currentNode->getNextNode();
    }
    public function rewind() {
        $this->position = 0;
        $this->currentNode = $this->startingNode;
    }
    public function valid() {
        if($this->currentNode === null) {
            return false;
        }
        if(! ($this->currentNode instanceof TimelineNode)) {
            return false;
        }
        return true;
    }
}