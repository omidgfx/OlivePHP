<?php namespace Olive\Security;

use Olive\manifest;

abstract class Crypt
{
    /**
     * @param $pw
     * @return string
     */
    public static function password($pw) {
        return self::password2(self::password1($pw));
    }

    /**
     * @param $pw
     * @return string
     */
    public static function password2($pw) {
        return strrev(md5(str_rot13($pw . strrev(manifest::HASH_SEED))));
    }

    /**
     * @param $pw
     * @return string
     */
    public static function password1($pw) {
        return strrev(md5($pw . manifest::HASH_SEED));
    }

    /**
     * @param $input
     * @return string
     */
    public static function encode64URL($input) {
        return urlencode(strtr(base64_encode($input), '+/=', '-_,'));
    }

    /**
     * @param $input
     * @return bool|string
     */
    public static function decode64URL($input) {
        return base64_decode(strtr(urldecode($input), '-_,', '+/='));
    }

    /**
     * @param $input
     * @param string $hashseed
     * @param string $method
     * @return string
     */
    public static function encrypt($input, $hashseed = manifest::HASH_SEED, $method = "AES-192-ECB") {
        return base64_encode(openssl_encrypt($input, $method, $hashseed));
    }

    /**
     * @param $encryptedInput
     * @param string $hashseed
     * @param string $method
     * @return null|string
     */
    public static function decrypt($encryptedInput, $hashseed = manifest::HASH_SEED, $method = "AES-192-ECB") {
        return openssl_decrypt(base64_decode($encryptedInput), $method, $hashseed);
    }
}