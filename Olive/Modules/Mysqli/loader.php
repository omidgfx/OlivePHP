<?php

require_once 'Engine/Exceptions.php';
require_once 'Engine/MySQLiConnection.php';
require_once 'Engine/Condition.php';
require_once 'Engine/DB.php';
require_once 'Engine/RecordInterface.php';
require_once 'Engine/Record.php';
require_once 'Engine/Model.php';
require_once 'Engine/View.php';


\Olive\Core::boot(__DIR__ . '/Models');
\Olive\Core::boot(__DIR__ . '/Views');