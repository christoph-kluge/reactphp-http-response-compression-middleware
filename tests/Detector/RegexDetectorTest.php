<?php

namespace Sikei\React\Tests\Http\Middleware\Detector;

use PHPUnit\Framework\TestCase;
use Sikei\React\Http\Middleware\Detector\RegexDetector;

class RegexDetectorTest extends TestCase
{

    public function testNothingWillDetectNothing()
    {
        $detector = new RegexDetector([]);

        $this->assertFalse($detector->isCompressible('something'));
    }

    public function testInvalidRegularExpression()
    {
        $detector = new RegexDetector(['^^$$']);

        $this->assertFalse($detector->isCompressible('a'));
    }

    public function testInvalidDetection()
    {
        $detector = new RegexDetector(['a', 'c']);

        $this->assertFalse($detector->isCompressible('b'));
    }

    public function testValidSimpleDetection()
    {
        $detector = new RegexDetector(['/^text\/html$/']);

        $this->assertTrue($detector->isCompressible('text/html'));
    }

    public function testValidRegexDetection()
    {
        $detector = new RegexDetector(['/^text\/[a-z]+$/']);

        $this->assertTrue($detector->isCompressible('text/html'));
    }
}
