<?php

use Olive\{Core, manifest};

# set defaults
date_default_timezone_set('GMT');
mb_language('uni');
mb_internal_encoding('UTF-8');

# Prepare manifest
require_once __DIR__ . '/../manifest.php';
require_once __DIR__ . '/References.php';

# Pre Boot parts
spl_autoload_register(static function ($psr) {
    /** @noinspection PhpIncludeInspection */
    require_once __DIR__ . '/../' . str_replace('\\', '/', $psr) . '.php';
}, true, true);

# Environment config
error_reporting(DEBUG_MODE ? E_ALL : 0);
set_error_handler([Core::class, 'errorHandler'], E_ALL);
register_shutdown_function([Core::class, 'shutdownHandler']);

# Prevent XML Attacks XXE
if (manifest::XXE_LIBXML_DISABLE_ENTITY_LOADER)
    libxml_disable_entity_loader();

# Start App
Core::startApp();
