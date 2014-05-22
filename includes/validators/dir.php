<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 4:01 PM
 */
require_once(VALIDATOR_INTERFACE_FILE);
class dir implements subValidator {
    public function validate($inValue, array $inOptions= array('removeDotDot' => true)) {
        if($inValue == '/') {
            return false;
        }
        if($inValue == '') {
            return false;
        }
        if($inValue == null) {
            return false;
        }
        if($inOptions['removeDotDot'] == true) {
            $inValue = str_replace('..', '', $inValue);
        }
        if(! is_dir(EDUCASK_ROOT . $inValue)) {
            return false;
        }
        return true;
    }
    public function hasOptions() {
        return true;
    }
}