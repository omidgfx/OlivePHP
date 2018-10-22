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
require_once 'Core/exceptions.php';
require_once 'Core/References.php';
require_once 'Core/Core.php';

set_error_handler(['Olive\Core', 'errorHandler'], E_ALL);
register_shutdown_function(['Olive\Core', 'shutdownHandler']);

# Core: Traits
require_once 'Core/Traits/Singleton.php';

# Core: http
require_once 'Core/Http/URL.php';
require_once 'Core/Http/Cookie.php';
require_once 'Core/Http/Session.php';
require_once 'Core/Http/Req.php';
require_once 'Core/Http/File.php';
require_once 'Core/Http/Linker.php';

# Core: Interfaces
require_once 'Core/Interfaces/Authenticatable.php';

# Core: routing
require_once 'Core/Routing/Controller.php';
require_once 'Core/Routing/Middleware.php';
require_once 'Core/Routing/Route.php';
require_once 'Core/Routing/RouteBypass.php';
require_once 'Core/Routing/RouteMiddler.php';
require_once 'Core/Routing/Router.php';

# Core: security
require_once 'Core/Security/Crypt.php';
require_once 'Core/Security/CSRFToken.php';

# Core: util
require_once 'Core/Util/DateTime.php';
require_once 'Core/Util/TimeLapse.php';
require_once 'Core/Util/Digit.php';
require_once 'Core/Util/Text.php';
require_once 'Core/Util/WithObject.php';

Core::boot();