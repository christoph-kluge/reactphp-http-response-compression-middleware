<?php

namespace Sikei\React\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

interface CompressionHandlerInterface
{

    /**
     * Check if the request can be handled by this
     * @param ServerRequestInterface $request
     * @return boolean
     */
    public function canHandle(ServerRequestInterface $request);

    /**
     * Checks wether a mime-type can be compressed
     * @param string $mime
     * @return mixed
     */
    public function isCompressible($mime);

    /**
     * Return the compression coding token (i.e. gzip, deflate, br, ...)
     * @see https://tools.ietf.org/html/rfc7230#section-4.2
     * @return string
     */
    public function __toString();

    /**
     * Invocation should attach an compressor to the stream and return the stream resource
     * @param StreamInterface $stream
     * @return StreamInterface
     */
    public function __invoke(StreamInterface $stream);
}
