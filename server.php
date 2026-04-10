<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists($publicPath.$uri) && !is_dir($publicPath.$uri)) {
    return false;
}

// On Windows, PHP's built-in server detects legacy subdirectories (e.g. STUDENT/)
// and incorrectly sets SCRIPT_NAME to something like /student/index.php.
// This causes Symfony's Request to strip the prefix from pathInfo, making Laravel
// route "/login" instead of "/student/login". Force the correct values so Symfony
// always sees baseUrl="" and routes the full URI path.
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $publicPath . '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';

require_once $publicPath.'/index.php';
