<?php
namespace Tests;

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
}

