<?php

namespace Sikei\React\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\HttpBodyStream;

class CompressionDeflateHandler implements CompressionHandlerInterface
{

    public function compressible(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept-Encoding');

        return stristr($accept, 'deflate') !== false;
    }

    public function __toString()
    {
        return 'deflate';
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
        $content = gzencode($content, -1, FORCE_DEFLATE);

        return \RingCentral\Psr7\stream_for($content);
    }
}
