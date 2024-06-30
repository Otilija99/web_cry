<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Repositories\User\UserRepository;
use App\Services\SqliteService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use FastRoute\RouteCollector;


try {
    $user = (new UserRepository(new SqliteService()))->findByUsername('Customer');
} catch (Exception $e) {
    echo 'Error fetching user: ', $e->getMessage();
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
]);

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/index', [App\Controllers\CurrencyController::class, 'index']);
    $r->addRoute('GET', '/currencies/{symbol}', [App\Controllers\CurrencyController::class, 'show']);
    $r->addRoute('POST', '/currency/search', [App\Controllers\CurrencyController::class, 'search']);
    $r->addRoute('POST', '/currency/buy', [App\Controllers\WalletController::class, 'buy']);
    // $r->addRoute('POST', '/wallets', [App\Controllers\CurrencyController::class, 'sell']);
    // $r->addRoute('GET', '/transactions', [App\Controllers\CurrencyController::class, 'change']);
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'] ?? null;
$uri = $_SERVER['REQUEST_URI'] ?? null;

if (!$httpMethod || !$uri) {
    echo 'HTTP method or URI not set';
    exit;
}

// Strip query string (?foo=bar) and decode URI
if (is_string($uri) && false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
if (is_string($uri)) {
    $uri = rawurldecode($uri);
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
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
        [$controller, $method] = $handler;
        if (!class_exists($controller)) {
            echo "Controller class $controller not found";
            exit;
        }
        $controllerInstance = new $controller($twig);
        if (!method_exists($controllerInstance, $method)) {
            echo "Method $method not found in controller $controller";
            exit;
        }
        $items = $controllerInstance->$method(...array_values($vars));
        echo $items;
        break;
    default:
        echo 'Unknown dispatch result';
        break;
}
