<?php namespace Olive\Http;

use Exception;
use Olive\manifest;

abstract class Cookie
{

    /**
     * @param string $name
     */
    public static function delete($name) {
        self::set($name, null, time() - 3600);
    }

    /**
     * @param string $name
     * @param $value
     * @param int|null $expires
     * @param string|null $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public static function setNative($name, $value, $expires = null, ?string $path = manifest::COOKIE_PATH, $domain = '', $secure = false, $httpOnly = true) {
        $expires = $expires ?? time() + manifest::COOKIE_EXPIRE;
        setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
    }

    public static function set(string $name, string $value = null, $expire = null, ?string $path = manifest::COOKIE_PATH, string $domain = null, bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = CookieHelper::SAMESITE_STRICT) {
        $expire = $expire ?? time() + manifest::COOKIE_EXPIRE;
        $ch     = new CookieHelper($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        header('Set-Cookie: ' . $ch);
    }

    /**
     * @param string $name
     * @param mixed $fallback
     * @return mixed
     */
    public static function get($name, $fallback = null) {
        return $_COOKIE[$name] ?? $fallback;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function exists($name) {
        return isset($_COOKIE[$name]);
    }

    /**
     * Clear all cookies
     * @return bool
     */
    public static function clear() {
        try {
            if (isset($_SERVER['HTTP_COOKIE'])) {
                $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
                foreach ($cookies as $cookie) {
                    $parts = explode('=', $cookie);
                    $name  = trim($parts[0]);
                    setcookie($name, '', time() - 1000);
                    setcookie($name, '', time() - 1000, '/');
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
