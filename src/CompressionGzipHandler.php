<?php

namespace Sikei\React\Http\Middleware;

use Clue\React\Zlib\ZlibFilterStream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\HttpBodyStream;

class CompressionGzipHandler implements CompressionHandlerInterface
{

    public function compressible(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept-Encoding');

        return stristr($accept, $this->__toString()) !== false;
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

        if ($body instanceof HttpBodyStream) {
            return new HttpBodyStream($body->pipe(
                ZlibFilterStream::createGzipCompressor(1)
            ), null);
        }

        return \RingCentral\Psr7\stream_for(
            gzencode($body->getContents(), -1, FORCE_GZIP)
        );
    }
}
