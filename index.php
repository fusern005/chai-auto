<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

// FOR DEBUGGING ONLY
if (isset($_GET['debug_routing'])) {
    echo "<pre>";
    print_r([
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? '',
        'PHP_SELF' => $_SERVER['PHP_SELF'] ?? '',
    ]);
    echo "</pre>";
    exit;
}

// Forward the request to the public folder
require_once __DIR__.'/public/index.php';
