<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/16/14
 * Time: 3:12 PM
 */
class userID implements subValidator {

    public function validate($inValue) {
        if (!is_numeric($inValue)) {

            return false;
        }
        if (preg_match('/\s/', $inValue)) {

            return false;
        }
        return true;
    }

    public function hasOptions() {
        // TODO: Implement hasOptions() method.
    }
}