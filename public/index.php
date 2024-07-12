<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Controller/ArticleController.php';
require __DIR__ . '/../src/Controller/CommentController.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use FastRoute\RouteCollector;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


$loader = new FilesystemLoader(__DIR__ . '\views');
$twig = new Environment($loader, ['cache' => false]);


$pdo = new PDO('sqlite:' . __DIR__ . '/../storage/database.sqlite');


$logger = new Logger('app');
$logger->pushProcessor(new UidProcessor());
$logger->pushHandler(new StreamHandler(__DIR__ . '/../storage/logs/app.log', Logger::DEBUG));


$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) {
    $routes = include('routes.php');
    foreach ($routes as $route) {
        [$method, $url, $handler] = $route;
        $r->addRoute($method, $url, $handler);
    }
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uri = strtok($uri, '?');

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo $twig->render('404.twig');
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        echo "405 Method Not Allowed";
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        [$controllerClass, $method] = $handler;

        $controller = new $controllerClass($pdo, $twig, $logger);

        if (method_exists($controller, $method)) {
            $response = $controller->$method(...array_values($vars));

            if (isset($response['template'], $response['data'])) {
                echo $twig->render($response['template'], $response['data']);
            } else {
                echo $response;
            }
        } else {
            http_response_code(500);
            echo "500 Internal Server Error";
        }
        break;
}


