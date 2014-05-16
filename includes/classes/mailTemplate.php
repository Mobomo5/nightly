<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 5/15/14
 * Time: 9:38 PM
 */

class mailTemplate {
    private $id;
    private $name;
    private $body;
    private $modifier;
    public function __construct($inID, $inName, $inBody, $inModifier) {
        if(! is_numeric($inID)) {
            return;
        }
        if($inID < 1) {
            return;
        }
        if(preg_match('/\s/', $inName)) {
            return;
        }
        if(! is_numeric($inModifier)) {
            return;
        }
        if($inModifier < 1) {
            return;
        }
        $this->id = $inID;
        $this->name = $inName;
        $this->body = $inBody;
        $this->modifier = $inModifier;
    }
    public function getID() {
        return $this->id;
    }
    public function getName() {
        return $this->name;
    }
    public function setName($inName) {
        if(preg_match('/\s/', $inName)) {
            return;
        }
        $this->name = $inName;
    }
    public function getBody() {
        return $this->body;
    }
    public function setBody($inBody) {
        $this->body = $inBody;
    }
    public function getModifier() {
        return $this->modifier;
    }
    public function setModifier($inModifier) {
        if(! is_numeric($inModifier)) {
            return;
        }
        if($inModifier < 1) {
            return;
        }
        $this->modifier = $inModifier;
    }
    public function __toString() {
        return $this->body;
    }
} 