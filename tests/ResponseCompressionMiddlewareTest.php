<?php

namespace Sikei\React\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\Response;
use React\Http\ServerRequest;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use function RingCentral\Psr7\stream_for;

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

    public function testCompressHandlerIsCalledWhenHeadersArePresent()
    {
        $content = 'not-compressed';
        $request = new ServerRequest('GET', 'https://example.com/', ['Accept-Encoding' => 'gzip, deflate, br, custom']);
        $response = new Response(200, [
            'Content-Type'   => 'text/html',
            'Content-Length' => strlen($content),
        ], $content);


        $token = 'custom';
        $return = 'compressed';
        $middleware = new ResponseCompressionMiddleware([
            $this->getCustomCompressionHandler($token, $return),
        ]);

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $result->then(function ($value) use (&$response) {
            $response = $value;
        });

        $this->assertNotNull($response);
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertTrue($response->hasHeader('Content-Encoding'));
        $this->assertSame($token, $response->getHeaderLine('Content-Encoding'));
        $this->assertSame($return, $response->getBody()->getContents());
    }

    public function testCompressHandlerIsNotCalledWhenHeadersArePresent()
    {
        $content = 'not-compressed';
        $request = new ServerRequest('GET', 'https://example.com/', ['Accept-Encoding' => 'gzip, deflate, br']);
        $response = new Response(200, [
            'Content-Type'   => 'text/html',
            'Content-Length' => strlen($content),
        ], $content);

        $token = 'custom';
        $return = 'compressed';
        $middleware = new ResponseCompressionMiddleware([
            $this->getCustomCompressionHandler($token, $return),
        ]);

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

    public function getCustomCompressionHandler($token, $invoke)
    {
        return new class ($token, $invoke) implements CompressionHandlerInterface
        {

            protected $token;
            protected $response;

            public function __construct($token, $response)
            {
                $this->token = $token;
                $this->response = $response;
            }

            public function compressible(ServerRequestInterface $request)
            {
                return stristr($request->getHeaderLine('Accept-Encoding'), (string)$this) !== false;
            }

            public function __toString()
            {
                return $this->token;
            }

            public function __invoke(StreamInterface $stream, $mime)
            {
                return stream_for($this->response);
            }
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
