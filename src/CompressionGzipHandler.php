<?php

namespace Sikei\React\Http\Middleware;

use Clue\React\Zlib\ZlibFilterStream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\EventLoop\LoopInterface;
use React\Http\HttpBodyStream;
use React\Stream\ReadableResourceStream;
use React\Stream\ThroughStream;

class CompressionGzipHandler implements CompressionHandlerInterface
{

    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function compressible(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept-Encoding');

        return stristr($accept, 'gzip') !== false;
    }

    public function __toString()
    {
        return 'gzip';
    }

    public function __invoke(StreamInterface $body, $mime)
    {
        if (!$body->isReadable()) {
            return $body;
        }

        $in = new ReadableResourceStream($body->detach(), $this->loop);
        $out = new ThroughStream();

        $compressor = ZlibFilterStream::createGzipCompressor(1);

        $in->pipe($compressor)->pipe($out);

        return new HttpBodyStream($out, null);
    }
}
