<?php

namespace Sikei\React\Tests\Http\Middleware;

use Clue\React\Zlib\ZlibFilterStream;
use PHPUnit\Framework\TestCase;
use React\Http\Io\HttpBodyStream;
use React\Http\Io\ServerRequest;
use React\Stream\ThroughStream;
use function RingCentral\Psr7\stream_for;
use Sikei\React\Http\Middleware\CompressionDeflateHandler;

class CompressionDeflateHandlerTest extends TestCase
{

    /** @var CompressionDeflateHandler */
    public $handler;

    public function setUp()
    {
        $this->handler = new CompressionDeflateHandler();
    }

    public function testHandlerHasCorrectToken()
    {
        $this->assertSame('deflate', (string)$this->handler);
    }

    public function testHandlerCanHandleClientCompressionMethods()
    {
        $request = new ServerRequest('GET', 'https://example.com/', [
            'Accept-Encoding' => 'deflate',
        ]);

        $this->assertTrue($this->handler->canHandle($request));
    }

    public function testHandlerCannotHandleClientsCompressionMethods()
    {
        $request = new ServerRequest('GET', 'https://example.com/', [
            'Accept-Encoding' => 'some-other',
        ]);

        $this->assertFalse($this->handler->canHandle($request));
    }

    public function testHandlerWillCompressHttpBodyStream()
    {
        $content = 'My test string';

        $stream = new ThroughStream();
        $body = new HttpBodyStream($stream, null);
        $body = $this->handler->__invoke($body);

        $body->on('data', function ($data) use (&$compressBuffer) {
            $compressBuffer .= $data;
        });

        $this->assertInstanceOf('React\Stream\ReadableStreamInterface', $body);

        $decompressor = ZlibFilterStream::createDeflateDecompressor();
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
        $body = $this->handler->__invoke($body);

        $this->assertInstanceOf('RingCentral\Psr7\Stream', $body);
        $compressed = $body->getContents();
        $this->assertSame($content, zlib_decode($compressed, strlen($compressed)));
    }
}
