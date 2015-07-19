<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 17/12/13
 * Time: 8:12 PM
 */
class Variable {
    private $name;
    private $value;
    private $readOnly;
    private $oldName;
    public function __construct($inName, $inValue, $inReadOnly = false) {
        if(! is_bool($inReadOnly)) {
            return;
        }
        $this->name = preg_replace('/\s+/', '', $inName);
        $this->value = $inValue;
        $this->readOnly = $inReadOnly;
        $this->oldName = null;
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
        if ($this->readOnly) {
            return;
        }
        $inName = preg_replace('/\s+/', '', $inName);
        if ($this->oldName === null) {
            $this->oldName = $this->name;
        }
        $this->name = $inName;
    }
    public function getOldName() {
        return $this->oldName;
    }
    public function setValue($inValue) {
        if ($this->readOnly) {
            return false;
        }
        $this->value = $inValue;
    }
    public function setReadOnly($inReadOnly = true) {
        if (!is_bool($inReadOnly)) {
            return;
        }
        $this->readOnly = $inReadOnly;
    }
    public function __toString() {
        return $this->value;
    }
    public function save() {
        return VariableEngine::getInstance()->saveVariable($this);
    }
}