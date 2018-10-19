<?php namespace Olive\MySQLi\Models;

use Olive\Auth\Authenticatable;
use Olive\MySQLi\Model;

class Test extends Model implements Authenticatable {

    /**
     * @return string
     */
    public static function table() {
        return 'test';
    }

    /**
     * @param $identifier
     * @return static
     * @throws \Olive\Exceptions\MySQLiAdaptingException
     * @throws \Olive\Exceptions\MySQLiConditionException
     * @throws \Olive\Exceptions\MySQLiException
     */
    public static function authGetByIdentifier($identifier) {
        return self::select(['a' => $identifier], TRUE);
    }

    /**
     * @param string $password
     * @param int $level 0,1,2
     * @return string
     */
    public static function authPasswordHash($password, $level = 0) {
        return $password;
    }

    /** @return string */
    public static function authIndentifierField() {
        return 'a';
    }

    /** @return string */
    public static function authPasswordField() {
        return 'b';
    }
}
