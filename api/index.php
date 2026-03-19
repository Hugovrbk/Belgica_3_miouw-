<?php
/**
 * Vercel PHP Router
 * Routes all requests to the appropriate PHP file in the project.
 */

$uri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rootDir = dirname(__DIR__); // Project root (parent of api/)

// Strip trailing slash (except root)
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

// Map URI to a PHP file
if ($uri === '/' || $uri === '') {
    chdir($rootDir);
    require $rootDir . '/index.php';
    exit;
}

$targetFile = $rootDir . $uri;

// If a PHP file exists at that path, execute it
if (file_exists($targetFile) && is_file($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    chdir(dirname($targetFile));
    require $targetFile;
    exit;
}

// Try adding .php extension
if (file_exists($targetFile . '.php') && is_file($targetFile . '.php')) {
    chdir(dirname($targetFile . '.php'));
    require $targetFile . '.php';
    exit;
}

// Fallback to homepage
chdir($rootDir);
require $rootDir . '/index.php';
