<?php
class honeypot {
    private $originalValue;
    private $inputtedValue;

    public function __construct($inInputtedValue='', $inOriginalValue='') {
        $this->originalValue = $inOriginalValue;
        $this->inputtedValue = $inInputtedValue;
    }

    public function inputValue($inValue) {
        $this->inputtedValue = $inValue;
    }

    public function changeOriginalValue($inValue) {
        $this->originalValue = $inValue;
    }

    public function validate() {
        if ($this->inputtedValue != $this->originalValue) {
            return false;
        }

        return true;
    }
}