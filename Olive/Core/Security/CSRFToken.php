<?php namespace Olive\Security;

use Olive\Exceptions\CSRFTokenExpired;
use Olive\Exceptions\CSRFTokenInvalid;
use Olive\Http\Session;
use Olive\Util\Text;

class CSRFToken {

    #region Fields
    /** @var string */
    protected $key = NULL;

    /** @var string */
    protected $token;

    /** @var int */
    protected $time;

    /**
     * CSRFToken constructor.
     * @param string $key (A-z, 0-9, _)
     */
    public function __construct($key = NULL) {
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
    public static function generate($key = NULL) {
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
    public static function check($token, $key = NULL, $timeout = NULL, $multiple = FALSE) {

        $csrf = self::read($key);
        if($csrf == NULL)
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
    public static function read($key = NULL) {

        $val = Session::get(static::fixKey($key));
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

    private static function token() {
        return Text::random(32);
    }

    private static function fixKey($key) {
        if($key === NULL)
            $key = '';
        $key .= '__ocsrf_';

        return preg_replace('/[^A-z0-9_]+/', '', $key);
    }

}