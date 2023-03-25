<?php
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule($container);
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => '127.0.0.1',
    'database' => 'makr',
    'username' => 'postgres',
    'password' => 'postgres',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => ''
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();