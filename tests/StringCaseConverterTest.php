<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Strucom\Tools\StringCaseConverter;
use InvalidArgumentException;

class StringCaseConverterTest extends TestCase
{
    // Tests for isValidCase with edge cases
    public function testEmptyWordsDigits(): void
    {
        $result = StringCaseConverter::isValidCase('0-0-', StringCaseConverter::KEBAB_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS | StringCaseConverter::ACCEPT_DIGITS);
        self::assertTrue($result);
    }

    public function testEmptyWordsLeading(): void
    {
        $result = StringCaseConverter::isValidCase('0_0_', StringCaseConverter::SNAKE_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS | StringCaseConverter::NO_LEADING_DIGITS);
        self::assertFalse($result);
    }

    public function testEmptyWordsValid(): void
    {
        $result = StringCaseConverter::isValidCase('__', StringCaseConverter::SNAKE_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    public function testDigitsNoLeading(): void
    {
        $result = StringCaseConverter::isValidCase('123abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS);
        self::assertFalse($result);
    }

    public function testDigitsAllowed(): void
    {
        $result = StringCaseConverter::isValidCase('123abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS);
        self::assertTrue($result);
    }

    public function testEmptyWordsTitle2(): void
    {
        $result = StringCaseConverter::isValidCase('_Title_Case_', StringCaseConverter::UNDERSCORE_TITLE_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    // Tests for exceptions in isValidCase
    public function testInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported format: invalidFormat');
        StringCaseConverter::isValidCase('test', 'invalidFormat', StringCaseConverter::VALIDATE);
    }

    public function testEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input string is empty and bitmask ALLOW_EMPTY is not set.');
        StringCaseConverter::convertCase('', StringCaseConverter::ANY_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE);
    }

    public function testInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input string "123abc" is not valid for PascalCase.');
        StringCaseConverter::convertCase('123abc', StringCaseConverter::PASCAL_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS);
    }

    // Tests for convertCase
    public function testConvertAnyToSnake(): void
    {
        $result = StringCaseConverter::convertCase('abCCddEE', StringCaseConverter::ANY_CASE, StringCaseConverter::SNAKE_CASE);
        self::assertSame('ab_c_cdd_e_e', $result);
    }

    public function testConvertCamelToSnake(): void
    {
        $result = StringCaseConverter::convertCase('abCCddEE', StringCaseConverter::CAMEL_CASE, StringCaseConverter::SNAKE_CASE);
        self::assertSame('ab_c_cdd_e_e', $result);
    }

    public function testConvertTitleToSnake(): void
    {
        $result = StringCaseConverter::convertCase('abCCddEE', StringCaseConverter::TITLE_CASE, StringCaseConverter::SNAKE_CASE, StringCaseConverter::SANITIZE);
        self::assertSame('ab_c_cdd_e_e', $result);
    }

    public function testConvertSnakeToSnake(): void
    {
        $result = StringCaseConverter::convertCase('ab_cc_dd_ee', StringCaseConverter::SNAKE_CASE, StringCaseConverter::SNAKE_CASE);
        self::assertSame('ab_cc_dd_ee', $result);
    }

    public function testConvertSnakeToCamel(): void
    {
        $result = StringCaseConverter::convertCase('ab_cc_dd_ee', StringCaseConverter::SNAKE_CASE, StringCaseConverter::CAMEL_CASE);
        self::assertSame('abCcDdEe', $result);
    }

    public function testConvertSnakeToTitle(): void
    {
        $result = StringCaseConverter::convertCase('ab_cc_dd_ee', StringCaseConverter::SNAKE_CASE, StringCaseConverter::TITLE_CASE);
        self::assertSame('Ab_Cc_Dd_Ee', $result);
    }

    public function testConvertSnakeToPascal(): void
    {
        $result = StringCaseConverter::convertCase('ab_cc_dd_ee', StringCaseConverter::SNAKE_CASE, StringCaseConverter::PASCAL_CASE);
        self::assertSame('AbCcDdEe', $result);
    }
}
