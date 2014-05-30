<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/30/14
 * Time: 1:15 PM
 */
class birthday implements subValidator {

    public function validate($inValue) {
        // validate indate. If it's outside of the 1912 - 2045 range, it's probably not a good date.
        if (!is_numeric($inValue)) {
            return false;
        }

        $early = strtotime('June 23, 1912'); // Alan Turing's birthday
        $late = strtotime('June 23, 2045'); // The Singularity

        if ($inValue < $early) {
            return false;
        }
        if ($inValue > $late) {
            return false;
        }
        return true;
    }

    public function hasOptions() {
        // TODO: Implement hasOptions() method.
    }
}