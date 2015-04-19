<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 05/01/14
 * Time: 9:44 PM
 */
require_once(GENERAL_FUNCTION_INTERFACE_FILE);

class generateRandomString implements generalFunction {
    public function run(array $inParams = array('randomLength' => false, 'length' => 5, 'minLength' => 256, 'maxLength' => 1024)) {
        if (empty($inParams)) {
            $inParams = array('length' => 5);
        }
        $length = $this->determineLength($inParams);
        $availableCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.';
        $availableCharacters = str_shuffle($availableCharacters);
        $randomString = '';
        $numberOfAvailableCharacters = strlen($availableCharacters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $availableCharacters[mt_rand(0, $numberOfAvailableCharacters)];
        }
        return $randomString;
    }
    private function determineLength($inParams) {
        if(isset($inParams['randomLength']) && ($inParams['randomLength'] == true)) {
            return $this->randomLength($inParams);
        }
        $defaultValue = 5;
        if(! isset($inParams['length'])) {
            return $defaultValue;
        }
        if(! is_numeric($inParams['length'])) {
            return $defaultValue;
        }
        return (int) $inParams['length'];
    }
    private function randomLength($inParams) {
        if(isset($inParams['minLength']) && is_numeric($inParams['minLength'])) {
            $minLength = (int) $inParams['minLength'];
        } else {
            $minLength = 256;
        }
        if(isset($inParams['maxLength']) && is_numeric($inParams['maxLength'])) {
            $maxLength = (int) $inParams['maxLength'];
        } else {
            $maxLength = 1024;
        }
        return mt_rand($minLength, $maxLength);
    }
    public function hasOptions() {
        return true;
    }
}