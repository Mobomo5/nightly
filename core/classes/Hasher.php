<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/9/14
 * Time: 8:07 PM
 */
require_once(EDUCASK_ROOT . '/core/thirdPartyLibraries/password/password.php');
class Hasher {
    public static function generateHash($thingToHash) {
        if(! is_string($thingToHash)) {
            return "";
        }
        return password_hash($thingToHash, PASSWORD_DEFAULT);
    }
    public static function verifyHash($nonHashed, $hashed) {
        if(! is_string($nonHashed)) {
            return false;
        }
        if(! is_string($hashed)) {
            return false;
        }
        if (!password_verify($nonHashed, $hashed)) {
            return false;
        }
        return true;
    }
    public static function doesItNeedRehashing($hash) {
        if(! is_string($hash)) {
            return false;
        }
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
    public static function generateHmacHash($toHash, $secretKey = null) {
        if(! is_string($toHash)) {
            return "";
        }
        if($secretKey === null) {
            $secretKey = Config::getInstance()->getAppKey();
        }
        if(! is_string($secretKey)) {
            return "";
        }
        return hash_hmac("sha512", $toHash, $secretKey);
    }
    public static function hmacVerify($nonHashed, $hashed, $secretKey = null) {
        if(! is_string($nonHashed)) {
            return false;
        }
        if(! is_string($hashed)) {
            return false;
        }
        if($secretKey === null) {
            $secretKey = Config::getInstance()->getAppKey();
        }
        if(! is_string($secretKey)) {
            return false;
        }
        $nonHashedHashed = hash_hmac("sha512", $nonHashed, $secretKey);
        if($nonHashedHashed !== $hashed) {
            return false;
        }
        return true;
    }
}