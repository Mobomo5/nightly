<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 06/01/14
 * Time: 9:44 PM
 */
require_once(VALIDATOR_INTERFACE_FILE);

class phpTimeZone implements subValidator {
    public function validate($inValue) {
        $timeZones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        if (!in_array($inValue, $timeZones)) {
            return false;
        }
        return true;
    }

    public function hasOptions() {
        return false;
    }
}