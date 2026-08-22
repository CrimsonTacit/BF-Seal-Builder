<?php
declare(strict_types=1);

/* Router for `php -S`, which is the local dev server and nothing else.

   Apache never loads this file, so anything here is development-only by
   construction -- which is what makes the /tests/ branch below safe. */

$root = dirname(__DIR__);
$site = $root . '/site';
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

/* The smoke suite lives in tests/, deliberately outside the webroot -- keeping
   it out of the served tree is half the reason site/ exists. It still has to be
   openable in a browser to run, so the dev server serves it and Apache does
   not. Resolved through realpath and confined to tests/, so a path with ../ in
   it cannot reach back out into the repo. */
if (str_starts_with($path, '/tests/')) {
    $base = realpath($root . '/tests');
    $file = realpath($root . $path);
    if ($base !== false && $file !== false
        && str_starts_with($file, $base . DIRECTORY_SEPARATOR) && is_file($file)) {
        $types = [
            'html' => 'text/html; charset=UTF-8',
            'css'  => 'text/css; charset=UTF-8',
            'js'   => 'text/javascript; charset=UTF-8',
            'json' => 'application/json',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
        ];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: no-store');   /* the suite's own {cache:"reload"} logic depends on it */
        readfile($file);
        return true;
    }
    http_response_code(404);
    return true;
}

/* php -S answers a path it does not recognise with the nearest index.php, so a
   typo'd or stale URL comes back 200 with the landing page rather than a 404 --
   which is not what Apache does, and is exactly the kind of false pass this
   suite exists to catch. Anything that is not a route, a test file, or a real
   file inside the webroot is a 404 here too. */
$real = realpath($site);
$file = realpath($site . $path);
if ($file === false || $real === false
    || !str_starts_with($file, $real . DIRECTORY_SEPARATOR) || !is_file($file)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "404 Not Found\n";
    return true;
}

return false;
