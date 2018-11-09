<?php namespace Olive\Security;

use Olive\Exceptions\CSRFTokenExpired;
use Olive\Exceptions\CSRFTokenInvalid;
use Olive\Http\Session;
use Olive\Util\Text;

class CSRFToken {

    #region Fields
    /** @var string */
    protected $key = null;

    /** @var string */
    protected $token;

    /** @var int */
    protected $time;

    /**
     * CSRFToken constructor.
     * @param string $key (A-z, 0-9, _)
     */
    public function __construct($key = null) {
        $this->key = $key;
    }


    /**
     * @return int
     */
    public function getTime() {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->token;
    }


    /**
     * @return string
     */
    public function getKey() {
        return $this->key;
    }


    /**
     * Renew the time of CSRFToken
     * @return static
     */
    public function expand() {
        $this->time = time();
        return $this;
    }

    /**
     * Re-Generate a new token
     * @return static
     */
    public function renew() {
        $this->token = static::token();
        return $this;
    }

    /**
     * Save CSRFToken in session
     * @return static
     */
    public function save() {
        Session::set(static::fixKey($this->key), "{$this->time}_$this->token");
        return $this;
    }

    /**
     * Revoke (remove) token from session
     */
    public function revoke() {
        Session::delete(static::fixKey($this->key));
    }
    #endregion

    /**
     * Generate a CSRF token
     *
     * @param string $key
     * @return static
     *
     * @uses Text,Session
     */
    public static function generate($key = null) {
        return (new static($key))
            ->expand()// time
            ->renew()// token
            ->save();
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
    public static function check($token, $key = null, $timeout = null, $multiple = false) {

        $csrf = self::read($key);
        if($csrf == null)
            throw new CSRFTokenInvalid;


        if(!$multiple) $csrf->revoke();

        $ok = $csrf->token === $token;

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
    public static function read($key = null) {

        $val = Session::get(static::fixKey($key));
        if($val == null) return null;

        $csrf      = new static;
        $csrf->key = $key;

        $val = explode('_', $val);
        if(count($val) != 2) return null;


        $time = $val[0];
        if(!is_numeric($time)) return null;

        $csrf->time = intval($time);

        $csrf->token = $val[1];
        if(strlen($csrf->token) != 32) return null;

        return $csrf;

    }

    private static function token() {
        return Text::random(32);
    }

    private static function fixKey($key) {
        if($key === null)
            $key = '';
        $key .= '__ocsrf_';

        return preg_replace('/[^A-z0-9_]+/', '', $key);
    }

}