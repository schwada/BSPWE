<?php

use App\Auth\{AuthenticationInterface, AuthenticationService};
use App\Middleware\AuthenticationMiddleware;
use Illuminate\Container\Container;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$container->singleton(AuthenticationInterface::class, fn($c) => new AuthenticationService($c));
$container->singleton(AuthenticationMiddleware::class, fn($c) => new AuthenticationMiddleware($c));

$container->bind("settings", function ($c) {
    return [
        'jwt' => [
            'secret' => ':XAstQoh7]O{X"j.oK+"=|yt&)&8rf:8Z6(J-()Qf!$<`UyR[?wf7xSz;B67-=j',
            'token_expiry' => 10, // minutes
            'algorithm' => 'HS256'
        ],
    ];
});

require_once __DIR__ . '/database.php';

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response->withHeader('Access-Control-Allow-Headers', '*')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
    ->withHeader('Access-Control-Allow-Credentials', 'true')
    ->withHeader('Access-Control-Allow-Origin', '*');
});

$app->addBodyParsingMiddleware();

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});


require_once __DIR__ . '/../routes/api.php';