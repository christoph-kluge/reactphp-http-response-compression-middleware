<?php

namespace Sikei\React\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

final class ResponseCompressionMiddleware
{

    /**
     * @var CompressionHandlerInterface[]
     */
    private $compressors;

    public function __construct(array $compressors = [])
    {
        $this->compressors = $compressors;

        foreach ($this->compressors as $compressor) {
            if (!$compressor instanceof CompressionHandlerInterface) {
                throw new \Exception('Passed invalid compressor');
            }
        }
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        if (!$request->hasHeader('Accept-Encoding')) {
            return $next($request);
        }

        $compressor = $this->getCompressor($request);
        if (!$compressor) {
            return $next($request);
        }

        $response = \React\Promise\resolve($next($request));
        return $response->then(function (Response $response) use ($compressor) {
            // response got already encoded
            if ($response->hasHeader('Content-Encoding')) {
                return $response;
            }

            // response has no content body (no need to compress anything)
            // reference: https://github.com/clue/php-zlib-react#inconsistencies
            // PHP 7 only: Compressing an empty string does not emit any data (not a valid compression stream)
            if ($response->hasHeader('Content-Length') && intval($response->getHeaderLine('Content-Length')) === 0) {
                return $response;
            }

            // compressible content-type is unknown
            if (!$response->hasHeader('Content-Type')) {
                return $response;
            }

            // response mime-type is not compressible
            $mime = $response->getHeaderLine('Content-Type');
            if (!$compressor->isCompressible($mime)) {
                return $response;
            }

            return $response
                ->withHeader('Content-Encoding', (string)$compressor)
                ->withBody($compressor($response->getBody()));
        });
    }

    private function getCompressor(ServerRequestInterface $request)
    {
        foreach ($this->compressors as $compressor) {
            if ($compressor->canHandle($request)) {
                return $compressor;
            }
        }
        return null;
    }
}
