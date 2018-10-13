<?php namespace Olive\Http;

use Olive\manifest;

abstract class Cookie {

    /**
     * @param string $key
     */
    public static function delete($key) {
        self::set($key, NULL, time() - 3600);
    }

    /**
     * @param string $key
     * @param $val
     * @param int|null $expire
     */
    public static function set($key, $val, $expire = NULL) {
        $expire = $expire === NULL ? time() + manifest::COOKIE_EXPIRE : $expire;
        setcookie($key, $val, $expire, manifest::COOKIE_PATH);
    }

    /**
     * @param string $key
     * @param mixed $fallback
     * @return mixed
     */
    public static function get($key, $fallback = NULL) {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $fallback;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function exists($key) {
        return isset($_COOKIE[$key]);
    }

    /**
     * Clear all cookies
     * @return bool
     */
    public static function clear() {
        try {
            if(isset($_SERVER['HTTP_COOKIE'])) {
                $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
                foreach($cookies as $cookie) {
                    $parts = explode('=', $cookie);
                    $name  = trim($parts[0]);
                    setcookie($name, '', time() - 1000);
                    setcookie($name, '', time() - 1000, '/');
                }
            }
            return TRUE;
        } catch(\Exception $e) {
            return FALSE;
        }
    }
}
