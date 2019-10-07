<?php namespace Olive\Support\Auth;

interface Authenticatable
{

    /**
     * @param $identifier
     * @return static
     */
    public static function authGetByIdentifier($identifier);

    /**
     * @param string $password
     * @param int $level
     * <br>1: password: pure, usage: save into session or cookie
     * <br>2: password: level 1 already hashed, usage: save into database
     * <br>3: passowrd: pure, shortcut for <span style="font-family:monospaced">hash(hash(pure,1),2)</span>
     * @return string
     */
    public static function authPasswordHash($password, $level = 1);

    /** @return string */
    public function authGetPassword();

}
