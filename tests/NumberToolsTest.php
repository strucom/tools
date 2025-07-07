<?php
namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Strucom\Tools\NumberTools;

class NumberToolsTest extends TestCase
{
    public function testIntBaseConvertDefaultBase(): void
    {
        // Test with default base (62)
        $result = NumberTools::intBaseConvert(12345);
        self::assertSame('3d7', $result);

        // Test with zero
        $result = NumberTools::intBaseConvert(0);
        self::assertSame('0', $result);

        // Test with a negative number
        $result = NumberTools::intBaseConvert(-12345);
        self::assertSame('-3d7', $result);
    }

    public function testIntBaseConvertCustomBase(): void
    {
        // Test with base 2 (binary)
        $result = NumberTools::intBaseConvert(10, 2);
        self::assertSame('1010', $result);

        // Test with base 16 (hexadecimal)
        $result = NumberTools::intBaseConvert(255, 16);
        self::assertSame('ff', $result);

        // Test with base 8 (octal)
        $result = NumberTools::intBaseConvert(64, 8);
        self::assertSame('100', $result);
    }

    public function testIntBaseConvertCustomDigits(): void
    {
        // Test with a custom digit set
        $result = NumberTools::intBaseConvert(123, 10, 'abcdefghij');
        self::assertSame('bcd', $result);

        // Test with a single-character digit set
        $result = NumberTools::intBaseConvert(5, 6, 'abcdef');
        self::assertSame('f', $result);
    }

    public function testIntBaseConvertInvalidBase(): void
    {
        // Test with base greater than the length of the digit set
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Base cannot be greater than the number of digits provided.');
        NumberTools::intBaseConvert(10, 100);

        // Test with base less than or equal to 0
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Base must be greater than 0.');
        NumberTools::intBaseConvert(10, 0);
    }

    public function testIntBaseConvertEmptyDigits(): void
    {
        // Test with an empty digit set
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Digits must be a non-empty string');
        NumberTools::intBaseConvert(10, 10, '');
    }

    public function testIntBaseConvertEdgeCases(): void
    {
        // Test with the smallest possible integer
        $result = NumberTools::intBaseConvert(PHP_INT_MIN, 10);
        self::assertStringStartsWith('-', $result);

        // Test with the largest possible integer
        $result = NumberTools::intBaseConvert(PHP_INT_MAX, 10);
        self::assertNotEmpty($result);
    }
}
