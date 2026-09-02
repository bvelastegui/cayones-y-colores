<?php

/**
 * Laravel development server router.
 *
 * This file allows `php artisan serve` to add the Service-Worker-Allowed
 * header required when the service worker lives under /build/ but must
 * control the root scope '/'.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

if ($uri === '/build/sw.js' && file_exists(__DIR__.'/public/build/sw.js')) {
    header('Service-Worker-Allowed: /');
    header('Content-Type: application/javascript');
    readfile(__DIR__.'/public/build/sw.js');

    return true;
}

// Serve static files directly when they exist.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
