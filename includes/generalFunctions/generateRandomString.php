<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 05/01/14
 * Time: 9:44 PM
 */
require_once(GENERAL_FUNCTION_INTERFACE_FILE);

class generateRandomString implements generalFunction {
    public function run(array $inParams = array('length' => 5)) {
        if (empty($inParams)) {
            $inParams = array('length' => 5);
        }
        if (!isset($inParams['length'])) {
            return;
        }
        $length = $inParams['length'];
        if (!is_numeric($length)) {
            return;
        }
        if ($length < 1) {
            return;
        }
        $availableCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.';
        $availableCharacters = str_shuffle($availableCharacters);
        $randomString = '';
        $numberOfAvailableCharacters = strlen($availableCharacters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $availableCharacters[mt_rand(0, $numberOfAvailableCharacters)];
        }
        return $randomString;
    }

    public function hasOptions() {
        return true;
    }
}