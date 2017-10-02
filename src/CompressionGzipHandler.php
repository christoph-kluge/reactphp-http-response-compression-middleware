<?php

namespace Sikei\React\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\HttpBodyStream;

class CompressionGzipHandler implements CompressionHandlerInterface
{

    public function compressible(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept-Encoding');

        return stristr($accept, 'gzip') !== false;
    }

    public function __toString()
    {
        return 'gzip';
    }

    public function __invoke(StreamInterface $stream, $mime)
    {
        if (!$stream->isWritable()) {
            return $stream;
        }

        if ($stream instanceof HttpBodyStream) {
            return $stream;
        }

        $content = $stream->getContents();
        $content = gzencode($content, -1, FORCE_GZIP);

        return \RingCentral\Psr7\stream_for($content);
    }
}
