<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 3:57 PM
 */
require_once(VALIDATOR_INTERFACE_FILE);
class email implements subValidator {
    public function validate($inValue) {
        if (!filter_var($inValue, FILTER_VALIDATE_EMAIL))
        {
            return false;
        }
        return true;
    }
    public function hasOptions() {
        return false;
    }
}