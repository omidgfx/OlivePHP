<?php namespace Olive\Support\Auth;

use Olive\Http\{Cookie, Response, Session, URL};
use Olive\manifest;

abstract class Auth {

    #region Consts and fields

    const SAVE_COOKIE  = 1;
    const SAVE_SESSION = 2;
    const SAVE_NOSAVE  = 0;

    /** @var Authenticatable */
    protected static $class = manifest::AUTH_AUTHENTICATABLE_CLASS;

    /** @var Authenticatable */
    public static $authenticated = null;
    #endregion

    #region Public mehtods

    /**
     * @param string $identifier
     * @param string $passowrd
     * @param int $save
     * @return AuthResult
     */
    public static function attempt($identifier, $passowrd, $save = self::SAVE_COOKIE) {

        # check
        $authResult = static::check($identifier, self::hash($passowrd, 3));
        self::setAuthenticated($authResult->authenticatable);

        if($authResult->isSucceed()) {
            $authString = static::encrypt($identifier, static::hash($passowrd, 1));
            if($save != static::SAVE_NOSAVE)
                self::save($authString, $save);
        }

        return $authResult;
    }

    /**
     * @return bool
     */
    public static function is() {

        $ret = function($state, $authenticatable = null) {
            if($state)
                static::setAuthenticated($authenticatable);
            else
                static::logout();
            return $state;
        };

        if(static::$authenticated !== null)
            return $ret(true, static::$authenticated);

        $restored = static::getSavedDecrypted();

        if($restored == null)
            return $ret(false);

        $identifier  = $restored[0][0];
        $hashedlvl_1 = $restored[0][1];
        $place       = $restored[1];
        $authString  = $restored[2];

        $authResult = static::check($identifier, static::hash($hashedlvl_1, 2));

        self::save($authString, $place);

        return $ret($authResult->isSucceed(), $authResult->authenticatable);

    }

    public static function logout() {
        Session::delete(manifest::AUTH_KEY);
        Cookie::delete(manifest::AUTH_KEY);
        static::setAuthenticated(null);
    }

    #endregion

    #region Protected methods
    /**
     * @param $identifier
     * @param $hashedPassword
     * @return AuthResult
     */
    protected static function check($identifier, $hashedPassword) {
        # get authenticatable
        $authenticatable = static::getAuthenticatable($identifier);

        # check for existance
        if($authenticatable == null)
            return new AuthResult(AuthResult::INVALID_IDENTIFIER);

        # get authenticatable stored password
        $p = $authenticatable->{static::$class::authPasswordField()};
        if($p != $hashedPassword)
            return new AuthResult(AuthResult::INVALID_PASSWORD);

        # success
        return new AuthResult(AuthResult::SUCCESS, $authenticatable);
    }

    /**
     * @param string $authString
     * @param string $place
     */
    protected static function save($authString, $place) {
        switch($place) {
            case static::SAVE_COOKIE:
                Cookie::set(manifest::AUTH_KEY, $authString);
                break;
            case static::SAVE_SESSION:
                Cookie::set(manifest::AUTH_KEY, $authString);
                break;
        }
    }

    /**
     * @return array|null
     */
    protected static function getSavedDecrypted() {
        # session
        $auth = static::decrypt($authString = Session::get(manifest::AUTH_KEY));
        if($auth !== null)
            return [$auth, static::SAVE_SESSION, $authString];

        # cookie
        $auth = static::decrypt($authString = Cookie::get(manifest::AUTH_KEY));
        if($auth !== null)
            return [$auth, static::SAVE_COOKIE, $authString];

        return null;
    }

    /**
     * @param string $identifier
     * @return Authenticatable
     */
    protected static function getAuthenticatable($identifier) {
        if(static::$authenticated === null)
            return static::$class::authGetByIdentifier($identifier);
        return static::$authenticated;
    }

    /**
     * @param Authenticatable $authenticated
     */
    protected static function setAuthenticated($authenticated) {
        static::$authenticated = $authenticated;
    }

    /**
     * @param string $identifier
     * @param string $password
     * @return string
     */
    protected static function encrypt($identifier, $password) {
        return base64_encode(
            base64_encode($identifier)
            . ':' .
            base64_encode($password)
        );
    }

    /**
     * @param string $authString
     * @return array|null
     */
    protected static function decrypt($authString) {
        if($authString == null)
            return null;
        $authString = base64_decode($authString);
        $authString = explode(':', $authString);

        if(count($authString) != 2) return null;

        return [
            base64_decode($authString[0]),
            base64_decode($authString[1]),
        ];

    }

    /**
     * @param string $password
     * @param int $level
     * @return string
     */
    protected static function hash($password, $level) {
        return static::$class::authPasswordHash($password, $level);
    }

    /**
     * @param string|array|URL $fallbackUrl see: {@see URL::parse}
     * @param string $fallbackUrlKey fallbackURL get key, null=skip ref
     * @throws \Olive\Exceptions\URLException
     */
    public static function prove($fallbackUrl = null, $fallbackUrlKey = 'ref') {
        $fallbackUrl = URL::parse($fallbackUrl);
        if(!is_null($fallbackUrlKey)) {
            $ref = $_SERVER['REQUEST_URI'];
            $fallbackUrl->addQuery($fallbackUrlKey, $ref);
        }
        if(!static::is())
            Response::redirect($fallbackUrl);
    }

    #endregion

}
