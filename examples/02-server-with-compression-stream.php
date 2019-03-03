<?php

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Io\MiddlewareRunner;
use React\Http\Response;
use React\Http\Server;
use React\Stream\ThroughStream;
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
    function (ServerRequestInterface $request, callable $next) use ($loop) {
        $stream = new ThroughStream();
        $loop->addTimer(0.001, function () use ($stream) {
            $stream->write('{');
            $stream->write('"some": "nice",');
        });

        $loop->addTimer(0.002, function () use ($stream) {
            $stream->write('"json": "values"');
            $stream->write('}');
            $stream->end();
        });

        return new Response(200, ['Content-Type' => 'application/json'], $stream);
    },
]));

$socket = new \React\Socket\Server(isset($argv[1]) ? $argv[1] : '0.0.0.0:0', $loop);
$server->listen($socket);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
