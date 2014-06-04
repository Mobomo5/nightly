<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/4/14
 * Time: 10:54 AM
 */

class nodeField {
    private $id;
    private $nodeFieldType;
    private $nodeType;
    private $weight;
    public function __construct($inID, nodeFieldType $inNodeFieldType, nodeType $inNodeType, $weight) {
        if(! is_numeric($inID)) {
            return;
        }
        if(! is_numeric($weight)) {
            return;
        }
        $this->id = $inID;
        $this->nodeFieldType = $inNodeFieldType;
        $this->nodeType = $inNodeType;
        $this->weight = $weight;
    }
    public function getID() {
        return $this->id;
    }
    public function getNodeFieldType() {
        return $this->nodeFieldType;
    }
    public function getNodeType() {
        return $this->nodeType;
    }
    public function getWeight() {
        return $this->weight;
    }
    public function setWeight($inWeight) {
       if(! is_numeric($inWeight)) {
           return;
       }
        $this->weight = $inWeight;
    }
} 