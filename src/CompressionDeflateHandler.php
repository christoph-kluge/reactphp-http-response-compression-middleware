<?php

namespace Sikei\React\Http\Middleware;

use Clue\React\Zlib\ZlibFilterStream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\HttpBodyStream;
use React\Stream\ReadableStreamInterface;

class CompressionDeflateHandler implements CompressionHandlerInterface
{

    public function canHandle(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept-Encoding');

        return stristr($accept, $this->__toString()) !== false;
    }

    public function isCompressible($mime)
    {
        return true;
    }

    public function __toString()
    {
        return 'deflate';
    }

    public function __invoke(StreamInterface $body)
    {
        if ($body instanceof ReadableStreamInterface) {
            return new HttpBodyStream($body->pipe(
                ZlibFilterStream::createDeflateCompressor(1)
            ), null);
        }

        return \RingCentral\Psr7\stream_for(
            gzencode($body->getContents(), -1, FORCE_DEFLATE)
        );
    }
}
