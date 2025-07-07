<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Strucom\Tools\StringCaseConverter;

class StringCaseConverterTest extends TestCase
{
    public function testEmptyAllowed(): void
    {
        $result = StringCaseConverter::isValidCase('', StringCaseConverter::ANY_CASE, StringCaseConverter::ALLOW_EMPTY);
        self::assertTrue($result);
    }

    public function testEmptyDenied(): void
    {
        $result = StringCaseConverter::isValidCase('', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testUnderscoreValid(): void
    {
        $result = StringCaseConverter::isValidCase('_', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testDashInvalid(): void
    {
        $result = StringCaseConverter::isValidCase('-', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testSnakeValid(): void
    {
        $result = StringCaseConverter::isValidCase('___', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
        $result = StringCaseConverter::isValidCase('___', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE|StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    public function testKebabInvalid(): void
    {
        $result = StringCaseConverter::isValidCase('_--', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testNoUnderscore(): void
    {
        $result = StringCaseConverter::isValidCase('camelCase', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testWithUnderscore2(): void
    {
        $result = StringCaseConverter::isValidCase('_camelCase', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testDigitsAllowed2(): void
    {
        $result = StringCaseConverter::isValidCase('0abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS);
        self::assertTrue($result);
    }

    public function testLeadingDigits(): void
    {
        $result = StringCaseConverter::isValidCase('0abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS);
        self::assertFalse($result);
    }

    public function testCamelValid(): void
    {
        $result = StringCaseConverter::isValidCase('camelCaseXXX', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
        $result = StringCaseConverter::isValidCase('camelCaseXXX', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE|StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    public function testPascalValid(): void
    {
        $result = StringCaseConverter::isValidCase('CamelCase', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testPascalInvalid(): void
    {
        $result = StringCaseConverter::isValidCase('camelCase', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testSnakeInvalid(): void
    {
        $result = StringCaseConverter::isValidCase('ab__AB', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
    }

    public function testSnakeValid2(): void
    {
        $result = StringCaseConverter::isValidCase('ab__ab', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testKebabValid(): void
    {
        $result = StringCaseConverter::isValidCase('ab-ab', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE);
        self::assertTrue($result);
    }

    public function testKebabInvalid2(): void
    {
        $result = StringCaseConverter::isValidCase('ab-ab-', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE);
        self::assertFalse($result);
        $result = StringCaseConverter::isValidCase('ab-ab-', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE|StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    public function testEmptyWordsAny(): void
    {
        $result = StringCaseConverter::isValidCase('-', StringCaseConverter::ANY_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    public function testEmptyWordsSnake(): void
    {
        $result = StringCaseConverter::isValidCase('__', StringCaseConverter::SNAKE_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    public function testEmptyWordsKebab(): void
    {
        $result = StringCaseConverter::isValidCase('--', StringCaseConverter::KEBAB_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    public function testEmptyWordsPascal(): void
    {
        $result = StringCaseConverter::isValidCase('', StringCaseConverter::PASCAL_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }

    public function testEmptyWordsTitle(): void
    {
        $result = StringCaseConverter::isValidCase('_Title_Case', StringCaseConverter::UNDERSCORE_TITLE_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS);
        self::assertTrue($result);
    }
}
