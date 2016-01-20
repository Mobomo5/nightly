<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 14/11/2015
 * Time: 7:24 PM
 */
class TimelineNode {
    private $timelineObject;
    private $nextNode;
    public function __construct(ITimelineObject $object) {
        $this->timelineObject = $object;
        $this->nextNode = null;
    }
    public function getObject() {
        return $this->timelineObject;
    }
    public function updateObject(ITimelineObject $object) {
        $this->timelineObject = $object;
    }
    public function getNextNode() {
        return $this->nextNode;
    }
    public function setNextNode(TimelineNode $node) {
        $this->nextNode = $node;
    }
    public function compareTo(TimelineNode $other) {
        return $this->timelineObject->compareTo($other->timelineObject);
    }
}