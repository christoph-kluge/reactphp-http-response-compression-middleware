<?php

namespace Sikei\React\Http\Middleware;

use Clue\React\Zlib\ZlibFilterStream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\Io\HttpBodyStream;
use React\Stream\ReadableStreamInterface;
use Sikei\React\Http\Middleware\Detector\DefaultRegexDetector;

class CompressionDeflateHandler implements CompressionHandlerInterface
{

    protected $detector;

    public function __construct(MimeDetectorInterface $detector = null)
    {
        $this->detector = $detector ?: new DefaultRegexDetector();
    }

    public function canHandle(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept-Encoding');

        return stristr($accept, $this->__toString()) !== false;
    }

    public function isCompressible($mime)
    {
        return $this->detector->isCompressible($mime);
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
