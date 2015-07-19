<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 24/05/2015
 * Time: 7:26 PM
 */
class Encrypter {
    const CIPHER = MCRYPT_RIJNDAEL_128; // Rijndael-128 is AES
    const MODE   = MCRYPT_MODE_CBC;
    private $key;
    public function __construct($inKey = null) {
        if(is_null($inKey)) {
            $this->key = Config::getInstance()->getAppKey();
            return;
        }
        if(! is_string($inKey)) {
            $this->key = Config::getInstance()->getAppKey();
            return;
        }
        $keyLength = mcrypt_get_key_size(self::CIPHER, self::MODE);
        if(strlen($inKey) > $keyLength) {
            $inKey = substr($inKey, 0, $keyLength);
        }
        $this->key = $inKey;
    }
    public function encrypt($toEncrypt) {
        if(! is_string($toEncrypt)) {
            return "";
        }
        $ivSize = mcrypt_get_iv_size(self::CIPHER, self::MODE);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_URANDOM);
        $encrypted = mcrypt_encrypt(self::CIPHER, $this->key, $toEncrypt, self::MODE, $iv);
        return trim(base64_encode($iv.$encrypted));
    }
    public function decrypt($toDecrypt) {
        if(! is_string($toDecrypt)) {
            return "";
        }
        $toDecrypt = base64_decode($toDecrypt);
        $ivSize = mcrypt_get_iv_size(self::CIPHER, self::MODE);
        if (strlen($toDecrypt) < $ivSize) {
            return "";
        }
        $iv = substr($toDecrypt, 0, $ivSize);
        $toDecrypt = substr($toDecrypt, $ivSize);
        $plaintext = mcrypt_decrypt(self::CIPHER, $this->key, $toDecrypt, self::MODE, $iv);
        return trim($plaintext, "\0");
    }
}