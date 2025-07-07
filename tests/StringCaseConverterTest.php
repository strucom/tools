<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Strucom\Tools\StringCaseConverter;

class StringCaseConverterTest extends TestCase
{
    public function testEmptyCaseAllow(): void
    {
        $result = StringCaseConverter::isValidCase('', StringCaseConverter::ANY_CASE, StringCaseConverter::ALLOW_EMPTY);
        self::assertTrue($result);
    }

    public function testEmptyCaseDeny(): void
    {
        $result = StringCaseConverter::isValidCase('', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testSpecialCharUnd(): void
    {
        $result = StringCaseConverter::isValidCase('_', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testSpecialCharDash(): void
    {
        $result = StringCaseConverter::isValidCase('-', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testSnakeCaseValid(): void
    {
        $result = StringCaseConverter::isValidCase('___', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testKebabCaseInvalid(): void
    {
        $result = StringCaseConverter::isValidCase('_--', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testMissingUnderscore(): void
    {
        $result = StringCaseConverter::isValidCase('camelCase', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testWithUnderscore(): void
    {
        $result = StringCaseConverter::isValidCase('_camelCase', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testDigitsAllowed(): void
    {
        $result = StringCaseConverter::isValidCase('0abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS);
        self::assertTrue($result);
    }

    public function testLeadingDigitsDeny(): void
    {
        $result = StringCaseConverter::isValidCase('0abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS);
        self::assertFalse($result);
    }

    public function testCamelCaseValid(): void
    {
        $result = StringCaseConverter::isValidCase('camelCaseXXX', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testPascalCaseValid(): void
    {
        $result = StringCaseConverter::isValidCase('CamelCase', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testPascalCaseInvalid(): void
    {
        $result = StringCaseConverter::isValidCase('camelCase', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testSnakeCaseInvalid(): void
    {
        $result = StringCaseConverter::isValidCase('ab__AB', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testSnakeCaseValid2(): void
    {
        $result = StringCaseConverter::isValidCase('ab__ab', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testKebabCaseValid(): void
    {
        $result = StringCaseConverter::isValidCase('ab-ab', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testKebabCaseInvalid2(): void
    {
        $result = StringCaseConverter::isValidCase('ab-ab-', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }
}
