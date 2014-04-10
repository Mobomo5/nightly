<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/9/14
 * Time: 8:07 PM
 */
require_once(EDUCASK_ROOT . '/thirdPartyLibraries/password/password.php');
class hasher {
    public function generateHash($thingToHash) {
        $thingToHash = md5(urlencode($thingToHash));
        return password_hash($thingToHash, PASSWORD_BCRYPT);
    }
    public function verifyHash($nonHashed, $hashed) {
        $nonHashed = md5(urlencode($nonHashed));
        if(! password_verify($nonHashed, $hashed)) {
            return false;
        }
        return true;
    }
    public function doesItNeedRehashing($hash) {
        return password_needs_rehash($hash, PASSWORD_BCRYPT);
    }
}