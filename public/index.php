<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
} elseif (file_exists($maintenance = __DIR__.'/../laravel_app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
if (file_exists($autoload = __DIR__.'/../vendor/autoload.php')) {
    require $autoload;
} elseif (file_exists($autoload = __DIR__.'/../laravel_app/vendor/autoload.php')) {
    require $autoload;
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
if (file_exists($bootstrap = __DIR__.'/../bootstrap/app.php')) {
    $app = require_once $bootstrap;
} elseif (file_exists($bootstrap = __DIR__.'/../laravel_app/bootstrap/app.php')) {
    $app = require_once $bootstrap;
} else {
    die('Laravel bootstrap file not found.');
}

$app->handleRequest(Request::capture());

