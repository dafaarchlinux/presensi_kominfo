<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/**
 * DEPLOY NOTE:
 * - Folder webroot (hosting): public_html/  -> berisi file ini + assets
 * - Folder app (hosting) di luar webroot: presensi_app/
 *
 * Kalau nama folder app kamu berbeda di hosting, ubah nilai $appPath di bawah.
 */
$appPath = realpath(__DIR__ . '/presensi_app');

if ($appPath === false) {
    http_response_code(500);
    echo "App path not found. Pastikan folder app ada di: " . __DIR__ . "/../presensi_app";
    exit;
}

// Maintenance mode
if (file_exists($maintenance = $appPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader
require $appPath . '/vendor/autoload.php';

// Bootstrap Laravel
/** @var Application $app */
$app = require_once $appPath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
