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

    public function __construct($token, $response)
    {
        $this->token = $token;
        $this->response = $response;
    }

    public function compressible(ServerRequestInterface $request)
    {
        return stristr($request->getHeaderLine('Accept-Encoding'), (string)$this) !== false;
    }

    public function __toString()
    {
        return $this->token;
    }

    public function __invoke(StreamInterface $stream, $mime)
    {
        return stream_for($this->response);
    }
}
