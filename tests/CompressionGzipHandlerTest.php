<?php

namespace Sikei\React\Http\Middleware;

use Clue\React\Zlib\ZlibFilterStream;
use PHPUnit\Framework\TestCase;
use React\Http\HttpBodyStream;
use React\Http\ServerRequest;
use React\Stream\ThroughStream;
use function RingCentral\Psr7\stream_for;

class CompressionGzipHandlerTest extends TestCase
{

    /** @var CompressionGzipHandler */
    public $handler;

    public function setUp()
    {
        $this->handler = new CompressionGzipHandler();
    }

    public function testHandlerHasCorrectToken()
    {
        $this->assertSame('gzip', (string)$this->handler);
    }

    public function testHandlerCanHandleClientCompressionMethods()
    {
        $request = new ServerRequest('GET', 'https://example.com/', [
            'Accept-Encoding' => 'gzip',
        ]);

        $this->assertTrue($this->handler->compressible($request));
    }

    public function testHandlerCannotHandleClientsCompressionMethods()
    {
        $request = new ServerRequest('GET', 'https://example.com/', [
            'Accept-Encoding' => 'some-other',
        ]);

        $this->assertFalse($this->handler->compressible($request));
    }

    public function testHandlerWillCompressHttpBodyStream()
    {
        $content = 'My test string';

        $stream = new ThroughStream();
        $body = new HttpBodyStream($stream, null);
        $body = $this->handler->__invoke($body, 'application/text');

        $body->on('data', function ($data) use (&$compressBuffer) {
            $compressBuffer .= $data;
        });

        $this->assertInstanceOf('React\Http\HttpBodyStream', $body);

        $decompressor = ZlibFilterStream::createGzipDecompressor();
        $decompressor->on('data', function ($data) use (&$decompressBuffer) {
            $decompressBuffer .= $data;
        });

        $body->pipe($decompressor);

        $stream->write($content);
        $stream->end();

        $this->assertNotSame($content, $compressBuffer);
        $this->assertSame($content, $decompressBuffer);
    }

    public function testHandlerWillCompressNonHttpBodyStream()
    {
        $content = 'My test string';

        $body = stream_for($content);
        $body = $this->handler->__invoke($body, 'application/text');

        $this->assertInstanceOf('RingCentral\Psr7\Stream', $body);
        $compressed = $body->getContents();
        $this->assertSame($content, zlib_decode($compressed, strlen($compressed)));
    }
}
