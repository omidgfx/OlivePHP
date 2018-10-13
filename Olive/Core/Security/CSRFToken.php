<?php namespace Olive\Security\CSRFTokenizer;

use Olive\Http\Session;
use Olive\Util\Text;

/**
 * Class CSRFToken
 * @package Olive\Security\CSRFTokenizer
 */
class CSRFToken {

    #region Constants
    const PREFIX = 'ocsrf_';
    #endregion

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

    public static function check($key, $token, $timeSpan=NULL, $multiple=false) {

    }

    /**
     * @param string $key
     * @return null|static
     *
     * @uses Session::get
     */
    public static function read($key) {

        $val = Session::get(static::fixkey($key));
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

    #region Private methods

    /**
     * @return static
     */
    private function save() {
        Session::set(static::PREFIX . $this->key, "{$this->time}_$this->token");
        return $this;
    }

    private static function fixkey($key) {
        return preg_replace('/[^a-zA-Z0-9_]+/', '', $key);
    }

    #endregion
}