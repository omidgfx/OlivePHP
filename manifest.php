<?php namespace Olive;

use Olive\Routing\RouteBypass;
use Olive\Routing\RouteMiddler;
use Olive\Routing\Router;

abstract class manifest
{

    #region Path

    # Should not starts or ends with slash,
    # unless your project stored in www or public_html or
    # server root directory, in that case you should use only one slash
    const ROOT_DIR = 'OlivePHP';

    # Only your domain name like site.com, sub.domain.com, localhost, etc.
    const DOMAIN = 'localhost';

    #endregion

    #region Routes and middlewares map
    public static function routerMapping(Router &$router) {
        $router->addMiddler(new RouteMiddler('mi'));
        $router->addBypass(new RouteBypass('', 'index'));
    }
    #endregion

    #region Cookie & Sessions
    const COOKIE_PATH   = '/';
    const COOKIE_EXPIRE = 864000; # 10 days
    # Automatically initialize sessions for every requests. RECOMANDED: TRUE
    const AUTO_INIT_SESSION = true;
    #endregion

    #region Security
    # Some random string for hashing. WARNING: use a unique hash_seed for each project, and never change it
    const HASH_SEED = 'some-random-text';
    #endregion

    #region MySQLi
    const MYSQLI_HOST     = 'localhost';
    const MYSQLI_DBNAME   = 'mydb';
    const MYSQLI_USERNAME = 'root';
    const MYSQLI_PASSWORD = '';
    #endregion

    #region Auth
    const AUTH_AUTHENTICATABLE_CLASS = '\Olive\MySQLi\Models\Test';
    const AUTH_KEY                   = 'olive_auth'; // use for coockies and sessions
    #endregion

    #region Date & Time
    const DEFAULT_TIMEZONE               = 'Asia/Tehran'; # Default timezone for date and time system
    const DEFAULT_CALENDAR               = 'persian'; # persian, gregorian, islamic
    const DEFAULT_DATETIME_LOCALE        = 'fa_IR'; # fa_IR, en_US
    const DEFAULT_DATETIME_PATTERN_SHORT = 'yyyy/MM/dd HH:mm:ss'; # based on ICU syntax format
    #endregion

}

define('DEBUG_MODE', true);