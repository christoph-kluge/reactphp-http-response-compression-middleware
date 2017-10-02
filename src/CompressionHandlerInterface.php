<?php

namespace Sikei\React\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

interface CompressionHandlerInterface
{

    /**
     * @param ServerRequestInterface $request
     * @return boolean
     */
    public function compressible(ServerRequestInterface $request);

    /**
     * Return the compression delegate token name (i.e. gzip, deflate, br, ...)
     * @return string
     */
    public function __toString();

    /**
     * Invocation should attach an compressor to the stream and return the stream resource
     * @param StreamInterface $stream
     * @param string          $mime
     * @return StreamInterface
     */
    public function __invoke(StreamInterface $stream, $mime);
}
