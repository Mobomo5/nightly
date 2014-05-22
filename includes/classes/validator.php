<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 3:36 PM
 */
class validator {
    private $subValidator;

    public function __construct($inType) {
        str_replace('..', '', $inType);
        $validatorFile = EDUCASK_ROOT . '/includes/validators/' . $inType . '.php';
        if (!is_file($validatorFile)) {
            $this->subValidator = false;
            return;
        }
        require_once($validatorFile);
        $this->subValidator = new $inType();
    }

    public function validate($inValue, array $inOptions = array()) {
        if (!$this->subValidator) {
            return false;
        }
        if (!is_object($this->subValidator)) {
            return false;
        }
        if (!in_array('subValidator', class_implements($this->subValidator))) {
            return false;
        }
        if (empty($inOptions)) {
            return $this->subValidator->validate($inValue);
        }
        if ($this->subValidator->hasOptions()) {
            return $this->subValidator->validate($inValue, $inOptions);
        }
        return $this->subValidator->validate($inValue);
    }

    public function validatorExists() {
        if (!$this->subValidator) {
            return false;
        }
        return true;
    }
}