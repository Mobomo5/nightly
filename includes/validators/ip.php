<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 8:33 PM
 */
require_once(VALIDATOR_INTERFACE_FILE);
class ip implements subValidator {
    public function validate($inValue) {
        if(! filter_var($inValue, FILTER_VALIDATE_IP)) {
            return false;
        }
        return true;
    }
    public function hasOptions() {
        return false;
    }
}