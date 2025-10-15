<?php

// Disable error display for production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\WeatherController;

// Set timezone
date_default_timezone_set('Europe/Kiev');

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

// Simple routing for PHP built-in server
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove query string from path for routing
$path = strtok($path, '?');

// Handle routing
if ($path === '/weather' || $path === '/index.php/weather') {
    $_SERVER['REQUEST_URI'] = '/weather';
    $controller = new WeatherController();
    $controller->handleRequest();
} elseif ($path === '/weather/coordinates' || $path === '/index.php/weather/coordinates') {
    $_SERVER['REQUEST_URI'] = '/weather/coordinates';
    $controller = new WeatherController();
    $controller->handleRequest();
} elseif ($path === '/weather/forecast' || $path === '/index.php/weather/forecast') {
    $_SERVER['REQUEST_URI'] = '/weather/forecast';
    $controller = new WeatherController();
    $controller->handleRequest();
} else {
    // Show weather interface
    readfile(__DIR__ . '/weather.html');
}
