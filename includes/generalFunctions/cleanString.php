<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 05/01/14
 * Time: 9:44 PM
 */
require_once(GENERAL_FUNCTION_INTERFACE_FILE);

class cleanString implements generalFunction {
    public function run(array $inParams = array()) {
        if (empty($inParams)) {
            return;
        }
        if (!isset($inParams['stringToClean'])) {
            return;
        }
        $string = $inParams['stringToClean'];
        $string = preg_replace('/[^A-Za-z0-9\-\&\/]/', '', htmlspecialchars($string));
        return $string;
    }

    public function hasOptions() {
        return true;
    }
}