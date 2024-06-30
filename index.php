<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Exceptions\HttpFailedRequestException;
use App\Exceptions\InvalidCurrencySymbolException;
use App\Exceptions\CurrencyNotFoundException;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$twigLoader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($twigLoader);

$routes = [
    ['GET', '/', [App\Controllers\CurrencyController::class, 'index']],
    ['GET', '/index', [App\Controllers\CurrencyController::class, 'index']],
    ['GET', '/currencies/{symbol}', [App\Controllers\CurrencyController::class, 'show']],
    ['POST', '/currency/search', [App\Controllers\CurrencyController::class, 'search']],
    ['POST', '/currency/buy', [App\Controllers\WalletController::class, 'buyCurrency']],
    ['POST', '/wallets', [App\Controllers\CurrencyController::class, 'sell']],
    ['GET', '/transactions', [App\Controllers\CurrencyController::class, 'change']],
];

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) use ($routes) {
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
});

$httpMethod = $_SERVER['REQUEST_METHOD'] ?? null;
$uri = $_SERVER['REQUEST_URI'] ?? null;


if (!$httpMethod || !$uri) {
    echo 'HTTP method or URI not set';
    exit;
}

if (is_string($uri) && false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
if (is_string($uri)) {
    $uri = rawurldecode($uri);
}


$routeInfo = $dispatcher->dispatch($httpMethod, $uri);


try {
    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            http_response_code(404);
            echo '404 Not Found';
            break;
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            http_response_code(405);
            echo '405 Method Not Allowed';
            break;
        case FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            [$controllerClass, $method] = $handler;
            if (!class_exists($controllerClass)) {
                echo "Controller class $controllerClass not found";
                exit;
            }

            // Pass the Twig environment to the controller constructor
            $controllerInstance = new $controllerClass($twig);

            if (!method_exists($controllerInstance, $method)) {
                echo "Method $method not found in controller $controllerClass";
                exit;
            }
            $request = Request::createFromGlobals();
            $response = $controllerInstance->$method($request, ...array_values($vars));
            echo $response;
            break;
        default:
            echo 'Unknown dispatch result';
            break;
    }
} catch (HttpFailedRequestException $e) {
    http_response_code(500);
    echo 'Error: ', $e->getMessage();
} catch (InvalidCurrencySymbolException $e) {
    http_response_code(400);
    echo 'Error: ', $e->getMessage();
} catch (CurrencyNotFoundException $e) {
    http_response_code(404);
    echo 'Error: ', $e->getMessage();
} catch (Exception $e) {
    http_response_code(500);
    echo 'An unexpected error occurred: ', $e->getMessage();
}
