<?php
require __DIR__ . "/../vendor/autoload.php";
use Illuminate\Database\Capsule\Manager as Capsule;
use Predis\Client;

// error_reporting(E_ALL);
// ini_set('ignore_repeated_errors', TRUE); // always use TRUE
// ini_set('display_errors', FALSE);
// ini_set('log_errors', TRUE); // Error/Exception file logging engine.
// ini_set('error_log', '/var/log/upcehosting-server.log'); // Logging file path

$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => '127.0.0.1',
    'database' => 'upcehosting',
    'username' => 'postgres',
    'password' => 'postgres',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => ''
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();



$redis = new Client("tcp:127.0.0.1:6379?read_write_timeout=0");
while (true) {
    $request = $redis->blpop('queue', 0);
    $data = json_decode($request[1], true);
    $operation = $data['operation'];
    $args = $data['args'];
    $args['db'] = $capsule;
    // try {
        $result = call_user_func($operation, $args);
    // } catch(Exception $e) {
        // $result = false;
    // }
    $log_string = json_encode(['operation' => $operation, 'args' => $args, 'success' => $result, 'timestamp' => time()]);
    file_put_contents('/var/log/upcehosting.log', $log_string . "\n", FILE_APPEND);
}