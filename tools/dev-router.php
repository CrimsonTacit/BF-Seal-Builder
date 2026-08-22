<?php
declare(strict_types=1);

$site = dirname(__DIR__) . '/site';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$routes = ['seal', 'header', 'banner', 'plaque', 'patch', 'mission'];
$route = ltrim($path, '/');

if ($path === '/') {
    require $site . '/index.php';
    return true;
}

if (in_array($route, $routes, true)) {
    require $site . '/' . $route . '.php';
    return true;
}

return false;
