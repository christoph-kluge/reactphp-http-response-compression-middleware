<?php

namespace Sikei\React\Http\Middleware\Detector;

use Sikei\React\Http\Middleware\MimeDetectorInterface;

class RegexDetector implements MimeDetectorInterface
{

    protected $expressions;

    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * Checks wether a mime-type can be compressed
     * @param string $mime
     * @return mixed
     */
    public function isCompressible($mime)
    {
        foreach ($this->expressions as $regex) {
            try {
                if (preg_match($regex, $mime)) {
                    return true;
                }
            } catch (\Exception $exception) {

            }
        }

        return false;
    }
}
