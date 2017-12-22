# ReactPHP Response Compression Middleware

[![Build Status](https://travis-ci.org/christoph-kluge/reactphp-http-response-compression-middleware.svg?branch=master)](https://travis-ci.org/christoph-kluge/reactphp-http-response-compression-middleware)
[![Total Downloads](https://poser.pugx.org/christoph-kluge/reactphp-http-response-compression-middleware/downloads)](https://packagist.org/packages/christoph-kluge/reactphp-http-response-compression-middleware)
[![License](https://poser.pugx.org/christoph-kluge/reactphp-http-response-compression-middleware/license)](https://packagist.org/packages/christoph-kluge/reactphp-http-response-compression-middleware)

# Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require christoph-kluge/reactphp-http-response-compression-middleware
```

This middleware will detect if the request is compressible and will compress the response body and add relevant headers to it. Heavy lifting is done by [clue/php-zlib-react](https://github.com/clue/php-zlib-react), thanks!

# Usage

```php
$server = new Server(new MiddlewareRunner([
    new ResponseCompressionMiddleware([
        new CompressionGzipHandler(),
        new CompressionDeflateHandler(),
    ]),
    function (ServerRequestInterface $request, callable $next) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'some' => 'nice',
            'json' => 'values',
        ]));
    },
]));
```

# Response-detection for compressible mime/content-types

## Default detection

The default handlers will use by default the `DefaultRegexDetecor` to identify compressible Content-Types by a set of regular expressions. Those regular expressions are:

```php
// DefaultRegexDetector.php -> used in __construct()

new RegexDetector([
    '/^text\/[a-z-\+]+$/', // text/*
    '/^application\/json$/', // application/json
    '/^application\/xml$/', // application/xml
    '/^[a-z-\+]+\/[a-z-\+]+\+json$/', // */*+json
    '/^[a-z-\+]+\/[a-z-\+]+\+xml$/', // */*+xml
]);
```

## Available Detectors

There are currently the following available detectors:

* `ArrayDetector`: Which will accept an whitelist of mime-types to check against
* `RegexDetecor`: Which will accept an whitelist of regular expressions to check against

```php
new CompressionGzipHandler(new ArrayDetector[
    'text/html',
]),
new CompressionGzipHandler(new RegexDetector[
    '/^text\/[a-z]+$/',
]),
```

## Want to implement custom detectors?

If you would like to add a custom detection for the response mime type then you can simply pass an object as first parameter which implements the `MimeDetectorInterface`.

```php
new CompressionGzipHandler(new class implements MimeDetectorInterface {
    public function isCompressible($mime) {
        // your custom magic here..
        return true;
    }
}),

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
