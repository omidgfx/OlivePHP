<?php namespace Olive;

# set defaults
date_default_timezone_set("GMT");
mb_language('uni');
mb_internal_encoding('UTF-8');


# Prepare manifest
require_once 'manifest.php';

# Environment config
error_reporting(DEBUG_MODE ? E_ALL : 0);

# Core
require_once 'Exceptions/Exceptions.php';
require_once 'References.php';
require_once 'Core.php';

set_error_handler(['Olive\Core', 'errorHandler'], E_ALL);
register_shutdown_function(['Olive\Core', 'shutdownHandler']);

# Core: Traits
require_once 'Traits/Singleton.php';

# Core: http
require_once 'Http/URL.php';
require_once 'Http/Cookie.php';
require_once 'Http/Session.php';
require_once 'Http/Req.php';
require_once 'Http/File.php';
require_once 'Http/Linker.php';

# Core: Interfaces
require_once 'Interfaces/Authenticatable.php';

# Core: routing
require_once 'Routing/Controller.php';
require_once 'Routing/Middleware.php';
require_once 'Routing/Route.php';
require_once 'Routing/RouteBypass.php';
require_once 'Routing/RouteMiddler.php';
require_once 'Routing/Router.php';

# Core: security
require_once 'Security/Crypt.php';
require_once 'Security/CSRFToken.php';

# Core: util
require_once 'Util/DateTime.php';
require_once 'Util/TimeLapse.php';
require_once 'Util/Digit.php';
require_once 'Util/Text.php';
require_once 'Util/WithObject.php';

Core::loadBootables('App');