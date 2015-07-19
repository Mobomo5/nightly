<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 06/01/14
 * Time: 9:44 PM
 */
class phpTimeZoneValidator implements IValidator {
    public function validate($inValue) {
        if(! is_string($inValue)) {
            return false;
        }
        $timeZones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        if (!in_array($inValue, $timeZones)) {
            return false;
        }
        return true;
    }
}