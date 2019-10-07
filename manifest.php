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
    public const ROOT_DIR = 'OlivePHP';

    #endregion

    #region Routes and middlewares map
    public static function routerMapping(Router $router): void {
        $router->addMiddler(new RouteMiddler('mi'));
        $router->addBypass(new RouteBypass('', 'index'));
    }
    #endregion

    #region Cookie & Sessions
    public const COOKIE_PATH   = '/';
    public const COOKIE_EXPIRE = 864000; # 10 days
    # Automatically initialize sessions for every requests. RECOMANDED: TRUE
    public const AUTO_INIT_SESSION = true;
    #endregion

    #region Security
    # Some random string for hashing. WARNING: use a unique hash_seed for each project, and never change it
    public const HASH_SEED = 'some-random-text';
    public const XXE_LIBXML_DISABLE_ENTITY_LOADER = true;
    #endregion

    #region MySQLi
    public const MYSQLI_HOST     = 'localhost';
    public const MYSQLI_DBNAME   = 'mydb';
    public const MYSQLI_USERNAME = 'root';
    public const MYSQLI_PASSWORD = '';
    #endregion

    #region Auth
    public const AUTH_KEY = 'olive_auth'; // use for coockies and sessions
    #endregion

    #region Date & Time
    public const DEFAULT_TIMEZONE               = 'Asia/Tehran'; # Default timezone for date and time system
    public const DEFAULT_CALENDAR               = 'persian'; # persian, gregorian, islamic
    public const DEFAULT_DATETIME_LOCALE        = 'fa_IR'; # fa_IR, en_US
    public const DEFAULT_DATETIME_PATTERN_SHORT = 'yyyy/MM/dd HH:mm:ss'; # based on ICU syntax format
    #endregion

}

define('DEBUG_MODE', true);
