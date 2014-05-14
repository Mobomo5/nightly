<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 17/12/13
 * Time: 8:12 PM
 */
class variable {
    private $name;
    private $value;
    private $readOnly;

    public function __construct($inName, $inValue, $inReadOnly = false) {
        $this->name = $inName;
        $this->value = $inValue;
        $this->readOnly = $inReadOnly;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function isReadOnly() {
        return $this->readOnly;
    }

    public function setName($inName) {
        if($this->readOnly) {
            return;
        }
        if(preg_match('/\s/', $inName)) {
            return;
        }
        $this->name = $inName;
    }

    public function setValue($inValue) {
        if($this->readOnly) {
            return;
        }
        $this->value = $inValue;
    }

    public function setReadOnly($inReadOnly = true) {
        if(! is_bool($inReadOnly)) {
            return;
        }
        $this->readOnly = $inReadOnly;
    }

    public function __toString() {
        return $this->value;
    }

    public function save() {
        return variableEngine::getInstance()->saveVariable($this);
    }
}