<?php

namespace Sikei\React\Tests\Http\Middleware\Detector;

use PHPUnit\Framework\TestCase;
use Sikei\React\Http\Middleware\Detector\ArrayDetector;

class ArrayDetectorTest extends TestCase
{

    public function testNothingWillDetectNothing()
    {
        $detector = new ArrayDetector([]);

        $this->assertFalse($detector->isCompressible('something'));
    }

    public function testInvalidArrayValuesExpression()
    {
        $detector = new ArrayDetector([
            0,
            1,
            false,
            true,
            new \stdClass(),
        ]);

        $this->assertFalse($detector->isCompressible('a'));
    }

    public function testInvalidDetection()
    {
        $detector = new ArrayDetector(['a', 'c']);

        $this->assertFalse($detector->isCompressible('b'));
    }

    public function testValidDetection()
    {
        $detector = new ArrayDetector(['a', 'c']);

        $this->assertTrue($detector->isCompressible('a'));
        $this->assertTrue($detector->isCompressible('c'));
    }
}
