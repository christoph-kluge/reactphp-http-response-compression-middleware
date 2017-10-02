# ReactPHP Response Compression Middleware

[![Build Status](https://travis-ci.org/christoph-kluge/reactphp-http-response-compression-middleware.svg?branch=master)](https://travis-ci.org/christoph-kluge/reactphp-http-response-compression-middleware)
[![Total Downloads](https://poser.pugx.org/christoph-kluge/reactphp-http-response-compression-middleware/downloads)](https://packagist.org/packages/christoph-kluge/reactphp-http-response-compression-middleware)
[![License](https://poser.pugx.org/christoph-kluge/reactphp-http-response-compression-middleware/license)](https://packagist.org/packages/christoph-kluge/reactphp-http-response-compression-middleware)

# Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require christoph-kluge/reactphp-http-response-compression-middleware
```

This middleware will detect if the request is compressible and will compress the response body and add relevant headers to it.

# Usage

```php
$server = new Server(new \React\Http\MiddlewareRunner([
    new \Sikei\React\Http\Middleware\ResponseCompressionMiddleware([
        new \Sikei\React\Http\Middleware\CompressionGzipHandler(),
        new \Sikei\React\Http\Middleware\CompressionDeflateHandler(),
    ]),
    function (ServerRequestInterface $request, callable $next) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'some' => 'nice',
            'json' => 'values',
        ]));
    },
]));
```

# License

The MIT License (MIT)

Copyright (c) 2017 Christoph Kluge

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
