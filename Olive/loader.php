<?php namespace Olive;

# set defaults
date_default_timezone_set("GMT");
mb_language('uni');
mb_internal_encoding('UTF-8');


# Prepare manifest
require_once 'manifest.php';
require_once 'References.php';
require_once 'Core.php';

$parts = ['Exceptions', 'Traits', 'Http', 'Routing', 'Security', 'Util'];
foreach($parts as $part) Core::boot("Olive/$part");

# Environment config
error_reporting(DEBUG_MODE ? E_ALL : 0);

set_error_handler(['Olive\Core', 'errorHandler'], E_ALL);
register_shutdown_function(['Olive\Core', 'shutdownHandler']);


Core::loadBootables('App');