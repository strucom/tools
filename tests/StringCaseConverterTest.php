<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Strucom\Tools\StringCaseConverter;
use InvalidArgumentException;

class StringCaseConverterTest extends TestCase
{
    /**
     * Tests for isValidCase with various edge cases, including ACCEPT_DIGITS_UC, ACCEPT_DIGITS_LC.
     */
    public function testIsValidCase(): void
    {
        // ACCEPT_DIGITS_UC
        self::assertFalse(StringCaseConverter::isValidCase('0abC', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_UC));
        self::assertTrue(StringCaseConverter::isValidCase('9a565', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_UC));
        self::assertTrue(StringCaseConverter::isValidCase('3a_Al', StringCaseConverter::TITLE_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_UC));
        self::assertFalse(StringCaseConverter::isValidCase('34_AB_', StringCaseConverter::TITLE_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_UC));
        self::assertFalse(StringCaseConverter::isValidCase('a53v', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_UC));

        // ACCEPT_DIGITS_LC
        self::assertTrue(StringCaseConverter::isValidCase('5f3v', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_LC));
        self::assertTrue(StringCaseConverter::isValidCase('R565', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_LC));
        self::assertFalse(StringCaseConverter::isValidCase('34_AB', StringCaseConverter::TITLE_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_LC));
        self::assertTrue(StringCaseConverter::isValidCase('34aa34P34', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS));
        self::assertFalse(StringCaseConverter::isValidCase('34aa34P34', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_UC));
        self::assertFalse(StringCaseConverter::isValidCase('34aa34P34', StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_LC));
        self::assertTrue(StringCaseConverter::isValidCase('34aa34P34', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS));
        self::assertFalse(StringCaseConverter::isValidCase('34aa34P34', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_UC));
        self::assertTrue(StringCaseConverter::isValidCase('34aa34P34', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_LC));

        // Empty words with digits
        self::assertTrue(StringCaseConverter::isValidCase('0-0-', StringCaseConverter::KEBAB_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS | StringCaseConverter::ACCEPT_DIGITS));
        self::assertFalse(StringCaseConverter::isValidCase('0_0_', StringCaseConverter::SNAKE_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS | StringCaseConverter::NO_LEADING_DIGITS));

        // Empty words without digits
        self::assertTrue(StringCaseConverter::isValidCase('__', StringCaseConverter::SNAKE_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS));
        self::assertTrue(StringCaseConverter::isValidCase('--', StringCaseConverter::KEBAB_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS));
        self::assertFalse(StringCaseConverter::isValidCase('--', StringCaseConverter::UNDERSCORE_KEBAB_CASE, StringCaseConverter::ALLOW_EMPTY_WORDS));

        // Digits handling
        self::assertFalse(StringCaseConverter::isValidCase('123abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS));
        self::assertTrue(StringCaseConverter::isValidCase('123abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS));
        self::assertFalse(StringCaseConverter::isValidCase('123abc', StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE));

        // Title case with empty words
        self::assertTrue(StringCaseConverter::isValidCase('_Title_Case_', StringCaseConverter::UNDERSCORE_TITLE_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ALLOW_EMPTY_WORDS));

        // Invalid format exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported format: invalidFormat');
        StringCaseConverter::isValidCase('test', 'invalidFormat', StringCaseConverter::VALIDATE);
    }

    /**
     * Tests for convertCase with various input and output formats, including ALLOW_INVALID_RESULT.
     */
    public function testConvertCase(): void
    {
        // Convert ANY_CASE to SNAKE_CASE
        self::assertSame('ab_c_cdd_ee', StringCaseConverter::convertCase('abCCddEE', StringCaseConverter::ANY_CASE, StringCaseConverter::SNAKE_CASE));

        // Convert CAMEL_CASE to SNAKE_CASE
        self::assertSame('ab_c_cdd_e_e', StringCaseConverter::convertCase('abCCddEE', StringCaseConverter::CAMEL_CASE, StringCaseConverter::SNAKE_CASE));

        // Convert TITLE_CASE to SNAKE_CASE with SANITIZE
        self::assertSame('abccddee', StringCaseConverter::convertCase('abCCddEE', StringCaseConverter::TITLE_CASE, StringCaseConverter::SNAKE_CASE, StringCaseConverter::SANITIZE));

        // Convert SNAKE_CASE to itself
        self::assertSame('ab_cc_dd_ee', StringCaseConverter::convertCase('ab_cc_dd_ee', StringCaseConverter::SNAKE_CASE, StringCaseConverter::SNAKE_CASE));

        // Convert SNAKE_CASE to CAMEL_CASE
        self::assertSame('abCcDdEe', StringCaseConverter::convertCase('ab_cc_dd_ee', StringCaseConverter::SNAKE_CASE, StringCaseConverter::CAMEL_CASE));

        // Convert SNAKE_CASE to TITLE_CASE
        self::assertSame('Ab_Cc_Dd_Ee', StringCaseConverter::convertCase('ab_cc_dd_ee', StringCaseConverter::SNAKE_CASE, StringCaseConverter::TITLE_CASE));

        // Convert SNAKE_CASE to PASCAL_CASE
        self::assertSame('AbCcDdEe', StringCaseConverter::convertCase('ab_cc_dd_ee', StringCaseConverter::SNAKE_CASE, StringCaseConverter::PASCAL_CASE));

        // ALLOW_INVALID_RESULT
        self::assertSame('123abc', StringCaseConverter::convertCase('123abc', StringCaseConverter::ANY_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::ALLOW_INVALID_RESULT));
        self::assertSame('abc', StringCaseConverter::convertCase('123abcüß5%', StringCaseConverter::ANY_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::SANITIZE));

        // Exception for empty string without ALLOW_EMPTY
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input string is empty and bitmask ALLOW_EMPTY is not set.');
        StringCaseConverter::convertCase('', StringCaseConverter::ANY_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE);

        // Exception for invalid input string
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input string "123abc" is not valid for PascalCase.');
        StringCaseConverter::convertCase('123abc', StringCaseConverter::PASCAL_CASE, StringCaseConverter::CAMEL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::NO_LEADING_DIGITS);

        // Exception for invalid output string
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Output string "ab_cc_dd_ee" is not valid for PascalCase.');
        StringCaseConverter::convertCase('0ab45', StringCaseConverter::CAMEL_CASE, StringCaseConverter::PASCAL_CASE, StringCaseConverter::VALIDATE | StringCaseConverter::ACCEPT_DIGITS_LC);
    }
}
