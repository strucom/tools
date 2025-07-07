<?php

use PHPUnit\Framework\TestCase;
use Strucom\Tools\StringCaseConverter;

class StringCaseConverterTest extends TestCase
{
    /**
     * @dataProvider provideIsValidCaseTestCases
     */
    public function testIsValidCase(string $input, string $format, int $validateInput, bool $expected): void
    {
        $result = StringCaseConverter::isValidCase($input, $format, $validateInput);
        self::assertSame($expected, $result);
    }

    public static function provideIsValidCaseTestCases(): array
    {
        return [
            // Empty cases
            ['', StringCaseConverter::ANY_CASE, StringCaseConverter::ALLOW_EMPTY, true],
            ['', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE, false],

            // Special characters
            ['_', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE, true],
            ['-', StringCaseConverter::ANY_CASE, StringCaseConverter::VALIDATE, true],
            ['___', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE, true],
            ['_--', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE, false],

            // Missing underscores in underscore-prefixed formats
            ['camelCase', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::VALIDATE, false],
            ['_camelCase', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::VALIDATE, true],
            ['PascalCase', StringCaseConverter::UNDERSCORE_PASCAL_CASE, StringCaseConverter::VALIDATE, false],
            ['_PascalCase', StringCaseConverter::UNDERSCORE_PASCAL_CASE, StringCaseConverter::VALIDATE, true],

            // Digits and leading digits
            ['0abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS, true],
            ['0abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS, false],
            ['abc0', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS, true],
            ['_0abc', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS, true],
            ['_0abc', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::VALIDATE, false],

            // CamelCase and PascalCase
            ['camelCaseXXX', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE, true],
            ['abcABCabc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE, true],
            ['CamelCase', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE, true],
            ['camelCase', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE, false],

            // Snake case
            ['ab__AB', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE, false],
            ['ab__ab', StringCaseConverter::SNAKE_CASE, StringCaseConverter::VALIDATE, true],

            // Kebab case
            ['ab-ab-', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE, false],
            ['ab-ab', StringCaseConverter::KEBAB_CASE, StringCaseConverter::VALIDATE, true],
        ];
    }

    /**
     * @dataProvider provideConvertCaseTestCases
     */
    public function testConvertCase(string $input, string $inFormat, string $outFormat, int $validateInput, string $expected): void
    {
        $result = StringCaseConverter::convertCase($input, $inFormat, $outFormat, $validateInput);
        self::assertSame($expected, $result);
    }

    public static function provideConvertCaseTestCases(): array
    {
        return [
            // Empty and special cases
            ['', StringCaseConverter::ANY_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::ALLOW_EMPTY, ''],
            ['_', StringCaseConverter::ANY_CASE, StringCaseConverter::SNAKE_CASE, StringCaseConverter::SANITIZE, '_'],
            ['-', StringCaseConverter::ANY_CASE, StringCaseConverter::KEBAB_CASE, StringCaseConverter::SANITIZE, '-'],
            ['___', StringCaseConverter::ANY_CASE, StringCaseConverter::SNAKE_CASE, StringCaseConverter::SANITIZE, '___'],
            ['_--', StringCaseConverter::ANY_CASE, StringCaseConverter::KEBAB_CASE, StringCaseConverter::SANITIZE, '_--'],

            // CamelCase and PascalCase
            ['camelCaseXXX', StringCaseConverter::CAMEL_CASE, StringCaseConverter::PASCAL_CASE, StringCaseConverter::SANITIZE, 'CamelCaseXXX'],
            ['camelCaseXXX', StringCaseConverter::CAMEL_CASE, StringCaseConverter::KEBAB_CASE, StringCaseConverter::SANITIZE, 'camel-case-x-x-x'],
            ['camelCaseXXX', StringCaseConverter::ANY_CASE, StringCaseConverter::KEBAB_CASE, StringCaseConverter::SANITIZE, 'camel-case-xxx'],
            ['abcABCabc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::SNAKE_CASE, StringCaseConverter::SANITIZE, 'abc_a_b_cabc'],

            // Snake case
            ['ab__AB', StringCaseConverter::SNAKE_CASE, StringCaseConverter::SCREAMING_SNAKE_CASE, StringCaseConverter::SANITIZE, 'AB__AB'],
            ['ab__ab', StringCaseConverter::SNAKE_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::SANITIZE, 'abAb'],

            // Edge cases with digits
            ['0_12_a', StringCaseConverter::SNAKE_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::SANITIZE | StringCaseConverter::ACCEPT_DIGITS, '012A'],
            ['0-3-', StringCaseConverter::KEBAB_CASE, StringCaseConverter::SNAKE_CASE, StringCaseConverter::SANITIZE | StringCaseConverter::ACCEPT_DIGITS, '0_3_'],
            ['ab-ab-', StringCaseConverter::KEBAB_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::SANITIZE, 'abAb'],
        ];
    }

    /**
     * @dataProvider provideExceptionTestCases
     */
    public function testConvertCaseThrowsException(string $input, string $inFormat, string $outFormat, int $validateInput, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        StringCaseConverter::convertCase($input, $inFormat, $outFormat, $validateInput);
    }

    public static function provideExceptionTestCases(): array
    {
        return [
            // Empty string without ALLOW_EMPTY
            ['', StringCaseConverter::ANY_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE, 'Input string is empty and bitmask ALLOW_EMPTY is not set.'],

            // Invalid formats
            ['camelCase', StringCaseConverter::UNDERSCORE_CAMEL_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE, 'Input string "camelCase" is not valid for _camelCase.'],
            ['PascalCase', StringCaseConverter::UNDERSCORE_PASCAL_CASE, StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE, 'Input string "PascalCase" is not valid for _PascalCase.'],

            // Leading digits not allowed
            ['0abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS, 'Input string "0abc" is not valid for camelCase.'],
        ];
    }
}
