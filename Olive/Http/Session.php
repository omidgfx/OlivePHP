<?php namespace Olive\Http;

use Olive\manifest;

abstract class Session
{
    /**
     * init sessions
     */
    public static function init() {
        self::start();

        if (!self::exists('olive_sess_ip') || self::get('olive_sess_ip') != $_SERVER['REMOTE_ADDR'])
            self::create();
    }

    private static function start() {
        @session_start();
    }

    private static function create() {
        self::clear();
        self::start();
        self::set('olive_sess_ip', $_SERVER['REMOTE_ADDR']);
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function exists($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * @param string $key
     * @param $val
     */
    public static function set($key, $val) {
        $_SESSION[$key] = $val;
    }

    /**
     * @param string $key
     * @param mixed $fallback
     * @return mixed|null $fallback
     */
    public static function get($key, $fallback = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $fallback;
    }

    /**
     * @param string $key
     */
    public static function delete($key) {
        unset($_SESSION[$key]);
    }


    /**
     * delete the session after getting
     *
     * @param string $key
     * @param null $fallback
     * @return mixed|null
     */
    public static function dispose($key, $fallback = null) {
        $v = self::get($key);
        unset($_SESSION[$key]);
        return $v == null ? $fallback : $v;
    }

    /**
     * Clear all sessions
     */
    public static function clear() {
        @session_unset();
        @session_destroy();
    }
}

if (manifest::AUTO_INIT_SESSION)
    Session::init();