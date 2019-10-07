<?php namespace Olive\Http;

use Olive\manifest;

abstract class Session
{
    public const SESSIONID = 'OLIVESID';

    /**
     * init sessions
     */
    public static function init() {
        self::start();

        if (!self::exists('olive_sess_ip') || self::get('olive_sess_ip') !== (string)$_SERVER['REMOTE_ADDR'])
            self::create();
    }

    private static function start() {
        @session_start([
            'cookie_httponly' => true,
            'cookie_path'     => '/',
            'name'            => self::SESSIONID,
        ]);
        $d   = session_get_cookie_params();
        $sid = session_id();
        Cookie::set(self::SESSIONID, $sid, 0, $d['path'], $d['domain']);
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
     * @param mixed $fallback
     * @return mixed|null $fallback
     */
    public static function get($key, $fallback = null) {
        return $_SESSION[$key] ?? $fallback;
    }

    private static function create() {
        self::clear();
        self::start();
        self::set('olive_sess_ip', $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Clear all sessions
     */
    public static function clear() {
        @session_unset();
        @session_destroy();
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
        return $v ?? $fallback;
    }
}

if (manifest::AUTO_INIT_SESSION)
    Session::init();
