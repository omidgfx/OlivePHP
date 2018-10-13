<?php

namespace Olive;

use Olive\Routing\RouteBypass;
use Olive\Routing\RouteMiddler;
use Olive\Routing\Router;

abstract class manifest {
    # --- Path
    const ROOT = 'OlivePHP'; # should NOT be started and ended with slash unless your project stored in www or public_html or root directory, in that case you should use one slash
    const HOST = 'localhost'; # should NOT be ended with slash (only domain root name valids here like site.com)

    public static function routerBypasses(Router &$router) {
        $router->addMiddler(new RouteMiddler('mi'));
        $router->addBypass(new RouteBypass('', 'index'));
    }

    # --- Cookie & Sessions
    const COOKIE_PATH       = '/';
    const COOKIE_EXPIRE     = 864000; # 10 days
    const AUTO_INIT_SESSION = TRUE; # Automatically initialize sessions for every requests. RECOMANDED: TRUE

    # --- Security
    const HASH_SEED = 'some-random-text'; # Some random string for hashing. WARNING: use a unique hash_seed for each project, and never change it

    # --- Data Engines
    const USE_MYSQLI  = TRUE;
    const USE_MONGODB = FALSE;
    const USE_SQLITE  = FALSE;

    const MYSQLI_HOST     = 'localhost';
    const MYSQLI_DBNAME   = 'mydb';
    const MYSQLI_USERNAME = 'root';
    const MYSQLI_PASSWORD = '';


    # --- Other
    const DEFAULT_TIMEZONE               = 'Asia/Tehran'; # Default timezone for date and time system
    const DEFAULT_CALENDAR               = 'persian'; # persian, gregorian, islamic
    const DEFAULT_DATETIME_LOCALE        = 'fa_IR'; # fa_IR, en_US
    const DEFAULT_DATETIME_PATTERN_SHORT = 'yyyy/MM/dd HH:mm:ss'; # based on ICU syntax format
}

define('DEBUG_MODE', TRUE);