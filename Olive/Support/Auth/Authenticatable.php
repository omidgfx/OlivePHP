<?php namespace Olive\Support\Auth;

interface Authenticatable {

    /**
     * @param $identifier
     * @return static
     */
    public static function authGetByIdentifier($identifier);

    /**
     * @param string $password
     * @param int $level 0,1,2
     * @return string
     */
    public static function authPasswordHash($password, $level = 0);

    /** @return string */
    public static function authPasswordField();

}