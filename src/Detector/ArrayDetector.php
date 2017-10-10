<?php

namespace Sikei\React\Http\Middleware\Detector;

use Sikei\React\Http\Middleware\MimeDetectorInterface;

class ArrayDetector implements MimeDetectorInterface
{

    protected $mimes;

    public function __construct(array $mimes)
    {
        $this->mimes = array_filter($mimes, function ($val) {
            return is_string($val);
        });
    }

    /**
     * Checks wether a mime-type can be compressed
     * @param string $mime
     * @return mixed
     */
    public function isCompressible($mime)
    {
        return in_array($mime, $this->mimes);
    }
}
