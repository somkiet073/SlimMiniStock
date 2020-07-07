<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';

//Override the default Not Found Handler before creating App
// $c['notFoundHandler'] = function ($c) {
//     return function ($request, $response) use ($c) {
//         return $response->withStatus(404)
//             ->withHeader('Content-Type', 'text/html')
//             ->write('Page not found');
//     };
// };

$app = new \Slim\App($settings);

// Basic Auth
// $app->add(new Tuupola\Middleware\HttpBasicAuthentication([
//     "users" => [
//         "admin" => "1234",
//         "user" => "1234"
//     ]
// ]));

// Set up dependencies
$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($app);

// Register middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// Run app
$app->run();
