<?php namespace Olive\Models;

Core::requireModule('Auth/Authenticatable');

use Olive\Core;use Olive\Interfaces\Authenticatable;
use Olive\Support\MySQLi\Model;

class Test extends Model implements Authenticatable {

    /**
     * @return string
     */
    public static function table() {
        return 'test';
    }

    /**
     * @return Test|Test[]
     * @throws \Olive\Exceptions\MySQLiAdaptingException
     * @throws \Olive\Exceptions\MySQLiConditionException
     * @throws \Olive\Exceptions\MySQLiException
     */
    public function getA() {
        return self::select([]);
}

    /**
     * @param $identifier
     * @return Authenticatable|Test|Test[]
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