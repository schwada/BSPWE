<?php

use App\Middleware\AuthenticationMiddleware;
use Slim\Routing\RouteCollectorProxy as Group;

$app->get('/', [App\Controllers\HomeController::class, 'index']);

$app->group('/auth', function (Group $group) {
    $group->post('/login', [App\Controllers\AuthenticationController::class, 'login']);
    $group->post('/logout', [App\Controllers\AuthenticationController::class, 'logout']);
    $group->post('/recover', [App\Controllers\AuthenticationController::class, 'recover']);
    $group->get('/me', [App\Controllers\AuthenticationController::class, 'get'])->add(AuthenticationMiddleware::class);
})->add(new App\Middleware\ExampleMiddleware());

$app->group('/users', function (Group $group) {
    $group->get('', [App\Controllers\UserController::class, 'index']);
    $group->post('', [App\Controllers\UserController::class, 'register']);
});

$app->group('/products', function (Group $group) {
    $group->get('', [App\Controllers\ProductController::class, 'index']);
    $group->post('', [App\Controllers\ProductController::class, 'create'])->add(AuthenticationMiddleware::class);;
    $group->get('/user', [App\Controllers\ProductController::class, 'userIndex'])->add(AuthenticationMiddleware::class);
    $group->get('/{id}', [App\Controllers\ProductController::class, 'read']);
});

// ->add(new AuthMiddleware($container->get(AuthenticationInterface::class));
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', App\Controllers\TestController::class);