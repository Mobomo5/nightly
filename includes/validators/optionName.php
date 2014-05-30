<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/16/14
 * Time: 3:14 PM
 */
class optionName implements subValidator {

    public function validate($inValue) {
        if (empty($inValue)) {
            return false;
        }
        if (preg_match('/\s/', $inValue)) {
            return false;
        }
        return true;
    }

    public function hasOptions() {
        return false;
    }
}