<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 8:33 PM
 */
class ipValidator implements IValidator {
    public function validate($inValue) {
        if(! is_string($inValue)) {
            return false;
        }
        if (!filter_var($inValue, FILTER_VALIDATE_IP)) {
            return false;
        }
        return true;
    }
}