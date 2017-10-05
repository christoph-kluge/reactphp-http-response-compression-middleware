<?php

namespace Sikei\React\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use React\Http\ServerRequest;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class ResponseCompressionMiddlewareTest extends TestCase
{

    public function testNoAutoCompressWhenNoSpecificHeadersArePresent()
    {
        $content = 'Some response';
        $request = new ServerRequest('GET', 'https://example.com/');
        $response = new Response(200, [], $content);

        $middleware = new ResponseCompressionMiddleware();

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $result->then(function ($value) use (&$response) {
            $response = $value;
        });

        $this->assertNotNull($response);
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertFalse($response->hasHeader('Content-Encoding'));
        $this->assertSame($content, $response->getBody()->getContents());
    }

    public function testCompressWhenGzipHeadersArePresent()
    {
        $this->markTestSkipped('This should implement a stub and move specific handler into own tests');

        $content = 'Some response';
        $request = new ServerRequest('GET', 'https://example.com/', ['Accept-Encoding' => 'gzip, deflate, br']);
        $response = new Response(200, [
            'Content-Type'   => 'text/html',
            'Content-Length' => strlen($content),
        ], $content);

        $middleware = new ResponseCompressionMiddleware([
            new CompressionGzipHandler(),
        ]);

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $result->then(function ($value) use (&$response) {
            $response = $value;
        });

        $this->assertNotNull($response);
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertTrue($response->hasHeader('Content-Encoding'));
        $this->assertSame('gzip', $response->getHeaderLine('Content-Encoding'));
        $this->assertSame($content, $this->gzdecode(
            $response->getBody()->getContents(),
            $response->getHeaderLine('Content-Length')
        ));
    }

    public function testMiddlewareSkipWhenGzipIsNotSupportedByClient()
    {
        $content = 'Some response';
        $request = new ServerRequest('GET', 'https://example.com/', ['Accept-Encoding' => 'deflate, br']);
        $response = new Response(200, [
            'Content-Type'   => 'text/html',
            'Content-Length' => strlen($content),
        ], $content);

        $middleware = new ResponseCompressionMiddleware();

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $result->then(function ($value) use (&$response) {
            $response = $value;
        });

        $this->assertNotNull($response);
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertFalse($response->hasHeader('Content-Encoding'));
        $this->assertSame($content, $response->getBody()->getContents());
    }

    public function testShouldSkipMiddlewareWhenResponseIsAlreadyCompressed()
    {
        $content = 'Some response';
        $request = new ServerRequest('GET', 'https://example.com/', ['Accept-Encoding' => 'deflate, br']);
        $response = new Response(200, [
            'Content-Type'     => 'text/html',
            'Content-Encoding' => 'br',
            'Content-Length'   => strlen($content),
        ], $content);

        $middleware = new ResponseCompressionMiddleware();

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $result->then(function ($value) use (&$response) {
            $response = $value;
        });

        $this->assertNotNull($response);
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertTrue($response->hasHeader('Content-Encoding'));
    }

    public function getNextCallback(Response $response)
    {
        return function (ServerRequestInterface $request) use (&$response) {
            return new Promise(function ($resolve, $reject) use ($request, &$response) {
                return $resolve($response);
            });
        };
    }

    public function gzdecode($data, $length = 0)
    {
        // php5.3 support for gzdecode (because it was added in php5.4)
        if (!function_exists('gzdecode')) {
            return gzinflate(substr($data, 10, -8), $length);
        }
        return gzdecode($data, $length);
    }
}
