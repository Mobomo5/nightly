<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 4:04 PM
 */
require_once(VALIDATOR_INTERFACE_FILE);
class file implements subValidator {
    public function validate($inValue) {
        if(! is_file(EHQ_SIMPLE_CMS_ROOT . $inValue)) {
            return false;
        }
        return true;
    }
    public function hasOptions() {
        return false;
    }
}