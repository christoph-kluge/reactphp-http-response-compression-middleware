<?php

namespace Sikei\React\Http\Middleware;

interface MimeDetectorInterface
{

    /**
     * Checks wether a mime-type can be compressed
     * @param string $mime
     * @return mixed
     */
    public function isCompressible($mime);
}
