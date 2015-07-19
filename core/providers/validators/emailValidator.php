<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 3:57 PM
 */
class emailValidator implements IValidator {
    public function validate($inValue) {
        if(! is_string($inValue)) {
            return false;
        }
        if (!filter_var($inValue, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }
}