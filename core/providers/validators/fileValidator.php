<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 4:04 PM
 */
class fileValidator implements IValidator {
    public function validate($inValue, $removeDotDot = true) {
        if(! is_string($inValue)) {
            return false;
        }
        if(! is_bool($removeDotDot)) {
            return false;
        }
        if ($inValue === '/') {
            return false;
        }
        if ($inValue === '') {
            return false;
        }
        if ($inValue === null) {
            return false;
        }
        if ($removeDotDot === true) {
            $inValue = str_replace('..', '', $inValue);
        }
        if (!is_file(EDUCASK_ROOT . $inValue)) {
            return false;
        }
        return true;
    }
}