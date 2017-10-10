<?php

namespace Sikei\React\Tests\Http\Middleware\Detector;

use PHPUnit\Framework\TestCase;
use Sikei\React\Http\Middleware\Detector\DefaultRegexDetector;
use Sikei\React\Http\Middleware\Detector\RegexDetector;

class DefaultRegexDetectorTest extends TestCase
{

    public function testDefaultRegexDetection()
    {
        $detector = new DefaultRegexDetector();

        $this->assertTrue($detector->isCompressible('text/html'));
        $this->assertTrue($detector->isCompressible('text/csv'));
        $this->assertTrue($detector->isCompressible('application/json'));
        $this->assertTrue($detector->isCompressible('application/xml'));
        $this->assertTrue($detector->isCompressible('application/calendar+json'));
        $this->assertTrue($detector->isCompressible('application/rdf+xml'));
    }
}
