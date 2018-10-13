<?php namespace Olive\Security\CSRFTokenizer;

use Olive\Exceptions\CSRFTokenExpired;
use Olive\Exceptions\CSRFTokenInvalid;
use Olive\Http\Session;
use Olive\Util\Text;

/**
 * Class CSRFToken
 * @package Olive\Security\CSRFTokenizer
 */
class CSRFToken {

    #region Fields
    /** @var string */
    public $key;

    /** @var string */
    public $token;

    /** @var int */
    public $time;
    #endregion

    #region Helpers

    /**
     * Generate a CSRF token
     *
     * @param string $key
     * @return static
     *
     * @uses Text,Session
     */
    public static function generate($key) {
        $csrf        = new CSRFToken;
        $csrf->key   = static::fixkey($key);
        $csrf->time  = time();
        $csrf->token = Text::random(32);
        return $csrf->save();

    }

    /**
     * @param $key
     * @param $token
     * @param null $timeout
     * @param bool $multiple
     * @return CSRFToken
     * @throws CSRFTokenExpired
     * @throws CSRFTokenInvalid
     */
    public static function check($key, $token, $timeout = NULL, $multiple = FALSE) {

        $csrf = self::read($key);
        if($csrf == NULL)
            throw new CSRFTokenInvalid;


        if(!$multiple) $csrf->revoke();

        $ok = $csrf->token == $token;

        if(!$ok)
            throw new CSRFTokenInvalid;

        if($timeout > 0 && $csrf->time + $timeout < time())
            throw new CSRFTokenExpired;

        return $csrf;
    }



    /**
     * @param string $key
     * @return null|static
     *
     * @uses Session::get
     */
    public static function read($key) {

        $val = Session::get(static::getName($key));
        if($val == NULL) return NULL;

        $csrf      = new static;
        $csrf->key = $key;

        $val = explode('_', $val);
        if(count($val) != 2) return NULL;


        $time = $val[0];
        if(!is_numeric($time)) return NULL;

        $csrf->time = intval($time);

        $csrf->token = $val[1];
        if(strlen($csrf->token) != 32) return NULL;

        return $csrf;

    }
    #endregion

    #region Non-static methods

    /**
     * @return static
     */
    public function save() {
        Session::set(static::getName($this->key), "{$this->time}_$this->token");
        return $this;
    }

    /**
     * @param string $key
     * @return string
     */
    private static function getName($key) {
        return '__osrcf_' . static::fixkey($key);
    }

    /**
     * Revoke (remove) token from session
     */
    public function revoke() {
        Session::delete(static::getName($this->key));
    }

    private static function fixkey($key) {
        return preg_replace('/[^a-zA-Z0-9_]+/', '', $key);
    }


    #endregion
}