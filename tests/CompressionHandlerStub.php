<?php

namespace Sikei\React\Tests\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use function RingCentral\Psr7\stream_for;
use Sikei\React\Http\Middleware\CompressionHandlerInterface;

class CompressionHandlerStub implements CompressionHandlerInterface
{

    protected $token;
    protected $response;
    protected $compressible;

    public function __construct($token, $response, $compressible)
    {
        $this->token = $token;
        $this->response = $response;
        $this->compressible = $compressible;
    }

    public function isCompressible($mime)
    {
        return (bool)$this->compressible;
    }

    public function canHandle(ServerRequestInterface $request)
    {
        return stristr($request->getHeaderLine('Accept-Encoding'), (string)$this) !== false;
    }

    public function __toString()
    {
        return $this->token;
    }

    public function __invoke(StreamInterface $stream)
    {
        return stream_for($this->response);
    }
}
