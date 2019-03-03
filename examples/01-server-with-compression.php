<?php

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Io\MiddlewareRunner;
use React\Http\Response;
use React\Http\Server;
use Sikei\React\Http\Middleware\ResponseCompressionMiddleware;
use Sikei\React\Http\Middleware\CompressionGzipHandler;
use Sikei\React\Http\Middleware\CompressionDeflateHandler;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$server = new Server(new MiddlewareRunner([
    new ResponseCompressionMiddleware([
        new CompressionGzipHandler(),
        new CompressionDeflateHandler(),
    ]),
    function (ServerRequestInterface $request, callable $next) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'some' => 'nice',
            'json' => 'values',
        ]));
    },
]));

$socket = new \React\Socket\Server(isset($argv[1]) ? $argv[1] : '0.0.0.0:0', $loop);
$server->listen($socket);

$server->on('error', function(Exception $error) {
    echo get_class($error) . ' - ' . $error->getMessage() . PHP_EOL;
    echo get_class($error->getPrevious()) . ' - ' . $error->getPrevious()->getMessage() . PHP_EOL;
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
