<?php

namespace Sikei\React\Http\Middleware\Detector;

use Sikei\React\Http\Middleware\MimeDetectorInterface;

class DefaultRegexDetector implements MimeDetectorInterface
{

    protected $detector;

    public function __construct()
    {
        $this->detector = new RegexDetector([
            '/^text\/[a-z-\+]+$/', // text/*
            '/^application\/json$/', // application/json
            '/^application\/xml$/', // application/xml
            '/^[a-z-\+]+\/[a-z-\+]+\+json$/', // */*+json
            '/^[a-z-\+]+\/[a-z-\+]+\+xml$/', // */*+xml
        ]);
    }

    /**
     * Checks wether a mime-type can be compressed
     * @param string $mime
     * @return mixed
     */
    public function isCompressible($mime)
    {
        return $this->detector->isCompressible($mime);
    }
}
