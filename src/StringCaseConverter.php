<?php

namespace Strucom\Tools;

use InvalidArgumentException;

/**
 * A utility class for converting strings between various case formats.
 *
 * This class supports multiple case formats, including camelCase, snake_case,
 * kebab-case, PascalCase, SCREAMING_SNAKE_CASE, Title_Case, and their underscore-prefixed variants.
 * It also supports ANY_CASE, which splits strings at underscores, hyphens, and changes between
 * lowercase and uppercase letters.
 *
 * @author af
 * @since PHP 7.0
 */
class StringCaseConverter
{
    // Case format constants
    public const string CAMEL_CASE = 'camelCase';
    public const string PASCAL_CASE = 'PascalCase';
    public const string SNAKE_CASE = 'snake_case';
    public const string TITLE_CASE = 'Title_Case';
    public const string SCREAMING_SNAKE_CASE = 'SCREAMING_SNAKE_CASE';
    public const string KEBAB_CASE = 'kebab-case';
    public const string TRAIN_CASE = 'Train-Case';
    public const string SCREAMING_KEBAB_CASE = 'SCREAMING-KEBAB-CASE';

    public const string ANY_CASE = 'any';

    // Underscore-prefixed constants
    public const string UNDERSCORE_CAMEL_CASE = '_camelCase';
    public const string UNDERSCORE_SNAKE_CASE = '_snake_case';
    public const string UNDERSCORE_KEBAB_CASE = '_kebab-case';
    public const string UNDERSCORE_PASCAL_CASE = '_PascalCase';
    public const string UNDERSCORE_SCREAMING_SNAKE_CASE = '_SCREAMING_SNAKE_CASE';
    public const string UNDERSCORE_TITLE_CASE = '_Title_Case';
    public const string UNDERSCORE_TRAIN_CASE = '_Train-Case';
    public const string UNDERSCORE_SCREAMING_KEBAB_CASE = '_SCREAMING-KEBAB-CASE';

// Validation constants (bitmask)
    public const int DO_NOT_VALIDATE   = 0b00000000; // 0
    public const int SANITIZE          = 0b00000001; // 1
    public const int VALIDATE          = 0b00000010; // 2
    public const int ACCEPT_DIGITS_UC  = 0b00000100; // 4
    public const int ACCEPT_DIGITS_LC  = 0b00001000; // 8
    public const int ACCEPT_DIGITS     = 0b00001100; // 12 (ACCEPT_DIGITS_UC | ACCEPT_DIGITS_LC)
    public const int NO_LEADING_DIGITS = 0b00010000; // 16
    public const int ALLOW_EMPTY_WORDS = 0b00100000; // 32
    public const int ALLOW_EMPTY       = 0b01100000; // 96 (includes ALLOW_EMPTY_WORDS)
    public const int ALLOW_INVALID_RESULT = 0b1000000; // 128



    /**
     * Converts a string from one case format to another.
     *
     * @param string $string    The input string to be converted.
     * @param string $inFormat  The format of the input string (use class constants).
     * @param string $outFormat The desired format of the output string (use class constants).
     * @param int    $validate  Validation mode as a bitmask (e.g., VALIDATE | ACCEPT_DIGITS).
     * @return string The converted string in the desired format.
     *
     * @throws InvalidArgumentException If validation fails and VALIDATE is set in the bitmask.
     * @since PHP 7.0
     * @author af
     */
    public static function convertCase(string $string, string $inFormat = self::ANY_CASE, string $outFormat = self::CAMEL_CASE, int $validate = self::SANITIZE): string
    {
        if (!self::isValidCase(string: $string, format: $inFormat, validate: $validate)) {
            if ($string === '') {
                throw new InvalidArgumentException('Input string is empty and bitmask ALLOW_EMPTY is not set.');
            }
            throw new InvalidArgumentException(sprintf('Input string "%s" is not valid for %s.', $string, $inFormat));
        }
        $lower = ($validate & self::ACCEPT_DIGITS_LC) ? 'a-z0-9' : 'a-z';
        $upper = ($validate & self::ACCEPT_DIGITS_UC) ? 'A-Z0-9' : 'A-Z';
        $string = self::sanitizeCase(string: $string, format: $inFormat, validate: $validate, lower: $lower, upper: $upper);
        $words = self::normalizeToWords(string: $string, format: $inFormat, lower: $lower, upper: $upper);
        $result = self::convertWordsToFormat(words: $words, format: $outFormat);
        if (($validate & self::ALLOW_INVALID_RESULT) || self::isValidCase(
                string: $result,
                format: $outFormat,
                validate: $validate)) {
            return $result;
        } else {
            throw new InvalidArgumentException(sprintf('Output string "%s" is not valid for %s.', $string, $outFormat));
        }
    }

    /**
     * Validates the input string for the specified case format.
     *
     * @param string $string   The input string.
     * @param string $format   The format of the input string.
     * @param int    $validate Validation mode as a bitmask (e.g., VALIDATE | ACCEPT_DIGITS).
     * @return bool True if the string is valid or bitmask does not include VALIDATE, false otherwise.
     *
     * @throws InvalidArgumentException If an unsupported format is provided.
     * @since PHP 7.0
     * @author af
     */
    public static function isValidCase(string $string, string $format, int $validate = self::VALIDATE): bool
    {
        if ($string === '') {
            return boolval($validate & self::ALLOW_EMPTY);
        }
        if (!($validate & self::VALIDATE)) {
            return true;
        }
        if (($validate & self::NO_LEADING_DIGITS) && preg_match('/^\d/', $string)) {
            return false;
        }
        $lower = ($validate & self::ACCEPT_DIGITS_LC) ? 'a-z0-9' : 'a-z';
        $upper = ($validate & self::ACCEPT_DIGITS_UC) ? 'A-Z0-9' : 'A-Z';
        if ($validate & self::ALLOW_EMPTY_WORDS) {
            return self::validateWithEmptyWords($string, $format, $lower, $upper);
        }
        return self::validateWithWords($string, $format, $lower, $upper);
    }

    /**
     * Converts an array of words to the desired format.
     *
     * @param array $words The array of words.
     * @param string $format The desired output format.
     * @return string The formatted string.
     *
     * @throws InvalidArgumentException If an unsupported output format is provided.
     * @since PHP 7.0
     * @author af
     */
    public static function convertWordsToFormat(array $words, string $format): string
    {
        return match ($format) {
            self::CAMEL_CASE => lcfirst(implode('', array_map(fn($word) => ucfirst(strtolower($word)), $words))),
            self::PASCAL_CASE => implode('', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::SNAKE_CASE => implode('_', array_map(fn($word) => strtolower($word), $words)),
            self::TITLE_CASE => implode('_', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::SCREAMING_SNAKE_CASE => strtoupper(implode('_', $words)),
            self::KEBAB_CASE => implode('-', array_map(fn($word) => strtolower($word), $words)),
            self::TRAIN_CASE => implode('-', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::SCREAMING_KEBAB_CASE => strtoupper(implode('-', $words)),
            self::UNDERSCORE_CAMEL_CASE => '_' . lcfirst(implode('', array_map(fn($word) => ucfirst(strtolower($word)), $words))),
            self::UNDERSCORE_PASCAL_CASE => '_' . implode('', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::UNDERSCORE_SNAKE_CASE => '_' . implode('_', array_map(fn($word) => strtolower($word), $words)),
            self::UNDERSCORE_TITLE_CASE => '_' . implode('_', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::UNDERSCORE_SCREAMING_SNAKE_CASE => '_' . strtoupper(implode('_', $words)),
            self::UNDERSCORE_KEBAB_CASE => '_' . implode('-', array_map(fn($word) => strtolower($word), $words)),
            self::UNDERSCORE_TRAIN_CASE => '_' . implode('-', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::UNDERSCORE_SCREAMING_KEBAB_CASE => '_' . strtoupper(implode('-', $words)),
            default => throw new InvalidArgumentException(sprintf('Unsupported format: %s', $format)),
        };
    }
    /**
     * Validates the string format when ALLOW_EMPTY_WORDS is set.
     *
     * @param string $string The input string.
     * @param string $format The format of the input string.
     * @param string $lower The character pattern treated as lower case.
     * @param string $upper The character pattern treated as upper case.
     *
     * @return bool True if the string matches the format, false otherwise.
     * @throws InvalidArgumentException If the format is unsupported.
     * @since PHP 7.0
     * @author af
     */
    private static function validateWithEmptyWords(string $string, string $format, string $lower, string $upper): bool
    {
        if (strlen($string) === 0) {
            return true;
        }
        if (in_array($format, [
        self::UNDERSCORE_CAMEL_CASE,
        self::UNDERSCORE_PASCAL_CASE,
        self::UNDERSCORE_SNAKE_CASE,
        self::UNDERSCORE_TITLE_CASE,
        self::UNDERSCORE_SCREAMING_SNAKE_CASE,
        self::UNDERSCORE_KEBAB_CASE,
        self::UNDERSCORE_TRAIN_CASE,
        self::UNDERSCORE_SCREAMING_KEBAB_CASE])) {
        if (!str_starts_with($string, '_')) {
            return false;
        }
        $string = substr($string, 1);
    }
        return match ($format) {
            self::ANY_CASE => preg_match("/^[_\-$lower$upper]*$/", $string) === 1,
            self::UNDERSCORE_CAMEL_CASE,
            self::CAMEL_CASE => preg_match("/^([$lower][$lower$upper]*)?$/", $string) === 1,
            self::UNDERSCORE_PASCAL_CASE,
            self::PASCAL_CASE => preg_match("/^[$upper][$lower$upper]*$/", $string) === 1,
            self::UNDERSCORE_SNAKE_CASE,
            self::SNAKE_CASE => preg_match("/^[_$lower]*$/", $string) === 1,
            self::UNDERSCORE_TITLE_CASE,
            self::TITLE_CASE => preg_match("/^([$upper][$lower]*)?(_[$upper][$lower]*)*$/", $string) === 1,
            self::UNDERSCORE_SCREAMING_SNAKE_CASE,
            self::SCREAMING_SNAKE_CASE => preg_match("/^[_$upper]*$/", $string) === 1,
            self::UNDERSCORE_KEBAB_CASE,
            self::KEBAB_CASE => preg_match("/^[\-$lower]*$/", $string) === 1,
            self::UNDERSCORE_TRAIN_CASE,
            self::TRAIN_CASE => preg_match("/^([$upper][$lower]*)?(-[$upper][$lower]*)*$/", $string) === 1,
            self::UNDERSCORE_SCREAMING_KEBAB_CASE,
            self::SCREAMING_KEBAB_CASE => preg_match("/^[\-$upper]*$/", $string) === 1,
            default => throw new InvalidArgumentException(sprintf('Unsupported format: %s', $format)),
        };
    }

    /**
     * Validates the string format when ALLOW_EMPTY_WORDS is not set.
     *
     * @param string $string The input string.
     * @param string $format The format of the input string.
     * @param string $lower The character pattern treated as lower case.
     * @param string $upper The character pattern treated as upper case.
     *
     * @return bool True if the string matches the format, false otherwise.
     * @throws InvalidArgumentException If the format is unsupported.
     * @since PHP 7.0
     * @author af
     */
    private static function validateWithWords(string $string, string $format, string $lower, string $upper): bool
    {
        if (in_array($format, [
            self::UNDERSCORE_CAMEL_CASE,
            self::UNDERSCORE_PASCAL_CASE,
            self::UNDERSCORE_SNAKE_CASE,
            self::UNDERSCORE_TITLE_CASE,
            self::UNDERSCORE_SCREAMING_SNAKE_CASE,
            self::UNDERSCORE_KEBAB_CASE,
            self::UNDERSCORE_TRAIN_CASE,
            self::UNDERSCORE_SCREAMING_KEBAB_CASE])) {
            if (!str_starts_with($string, '_')) {
                return false;
            }
            $string = substr($string, 1);
        }
        return match ($format) {
            self::ANY_CASE => preg_match("/^[$lower$upper]+([_\-]?[$lower$upper]+)*$/", $string) === 1,
            self::UNDERSCORE_CAMEL_CASE,
            self::CAMEL_CASE => preg_match("/^[$lower][$lower$upper]*$/", $string) === 1,
            self::UNDERSCORE_PASCAL_CASE,
            self::PASCAL_CASE => preg_match("/^[$upper][$lower$upper]*$/", $string) === 1,
            self::UNDERSCORE_SNAKE_CASE,
            self::SNAKE_CASE => preg_match("/^[$lower]+(_[$lower]+)*$/", $string) === 1,
            self::UNDERSCORE_TITLE_CASE,
            self::TITLE_CASE => preg_match("/^[$upper][$lower]*(_[$upper][$lower]*)*$/", $string) === 1,
            self::UNDERSCORE_SCREAMING_SNAKE_CASE,
            self::SCREAMING_SNAKE_CASE => preg_match("/^[$upper]+(_[$upper]+)*$/", $string) === 1,
            self::UNDERSCORE_KEBAB_CASE,
            self::KEBAB_CASE => preg_match("/^[$lower]+(-[$lower]+)*$/", $string) === 1,
            self::UNDERSCORE_TRAIN_CASE,
            self::TRAIN_CASE => preg_match("/^[$upper][$lower]*(-[$upper][$lower]*)*$/", $string) === 1,
            self::UNDERSCORE_SCREAMING_KEBAB_CASE,
            self::SCREAMING_KEBAB_CASE => preg_match("/^[$upper]+(-[$upper]+)*$/", $string) === 1,
            default => throw new InvalidArgumentException(sprintf('Unsupported format: %s', $format)),
        };
    }


    /**
     * Sanitizes the input string for the specified case format.
     *
     * This function removes characters that are not valid for the specified case format.
     * It does not guarantee that the returned string will strictly adhere to the given format.
     * For example, it will remove invalid characters but will not enforce capitalization or structure rules.
     *
     * @param string $string   The input string to be sanitized.
     * @param string $format   The format of the input string (use class constants).
     * @param int    $validate Validation mode as a bitmask (e.g., VALIDATE | ACCEPT_DIGITS).
     * @return string The sanitized string with offending characters removed.
     *
     * @throws InvalidArgumentException If an unsupported format is provided.
     * @since PHP 7.0
     * @author af
     */
    private static function sanitizeCase(string $string, string $format, int $validate, string $lower, string $upper): string
    {
        if (!($validate & self::SANITIZE)) {
            return $string;
        }

        // Remove leading digits if NO_LEADING_DIGITS is set
        if ($validate & self::NO_LEADING_DIGITS) {
            $string = ltrim($string, '0123456789');
        }
        if (in_array($format, [
            self::UNDERSCORE_CAMEL_CASE,
            self::UNDERSCORE_PASCAL_CASE,
            self::UNDERSCORE_SNAKE_CASE,
            self::UNDERSCORE_TITLE_CASE,
            self::UNDERSCORE_SCREAMING_SNAKE_CASE,
            self::UNDERSCORE_KEBAB_CASE,
            self::UNDERSCORE_TRAIN_CASE,
            self::UNDERSCORE_SCREAMING_KEBAB_CASE])) {
            if (str_starts_with($string, '_')) {
                $string = substr($string, 1);
            }
        }

        // Sanitize the string based on the format
        $separator = match ($format) {
            self::ANY_CASE => '\-_',
            self::UNDERSCORE_CAMEL_CASE,
            self::CAMEL_CASE,
            self::UNDERSCORE_PASCAL_CASE,
            self::PASCAL_CASE => '',
            self::UNDERSCORE_SNAKE_CASE,
            self::SNAKE_CASE,
            self::UNDERSCORE_TITLE_CASE,
            self::TITLE_CASE,
            self::UNDERSCORE_SCREAMING_SNAKE_CASE,
            self::SCREAMING_SNAKE_CASE => '_',
            self::UNDERSCORE_KEBAB_CASE,
            self::KEBAB_CASE,
            self::UNDERSCORE_TRAIN_CASE,
            self::TRAIN_CASE,
            self::UNDERSCORE_SCREAMING_KEBAB_CASE,
            self::SCREAMING_KEBAB_CASE => '\-',
            default => throw new InvalidArgumentException(sprintf('Unsupported format: %s', $format)),
        };
        $sanitized = preg_replace("/[^$separator$lower$upper]/", '', $string);
        if (in_array($format, [
            self::UNDERSCORE_CAMEL_CASE,
            self::UNDERSCORE_PASCAL_CASE,
            self::UNDERSCORE_SNAKE_CASE,
            self::UNDERSCORE_TITLE_CASE,
            self::UNDERSCORE_SCREAMING_SNAKE_CASE,
            self::UNDERSCORE_KEBAB_CASE,
            self::UNDERSCORE_TRAIN_CASE,
            self::UNDERSCORE_SCREAMING_KEBAB_CASE])) {
            $sanitized = '_' . $sanitized;

        }
        if (!($validate & self::ALLOW_EMPTY_WORDS)) {
            $sanitized = rtrim($sanitized, '_-');
        }
        return $sanitized;
    }


    /**
     * Normalizes a string to an array of words in lower case based on the input format.
     *
     * This function splits the input string into an array of words based on the specified format.
     * For `ANY_CASE`, it splits the string at underscores (`_`), hyphens (`-`), and changes between
     * lowercase and uppercase letters.
     *
     * @param string $string The input string.
     * @param string $format The format of the input string.
     * @return array The normalized array of words in lower case.
     *
     * @throws InvalidArgumentException If an unsupported input format is provided.
     * @since PHP 7.0
     * @author af
     */
    private static function normalizeToWords(string $string, string $format, string $lower, string $upper): array
    {
        return match ($format) {
            self::ANY_CASE => array_map(
                fn($item) => strtolower($item),
                preg_split("/[_\-]|(?<=[$lower])(?=[$upper])|(?<=[$upper])(?=[$upper][$lower])/", $string)
            ),
            self::CAMEL_CASE, self::PASCAL_CASE =>
            array_map(fn($item) => strtolower($item), preg_split("/(?=[$upper])/", lcfirst($string))),
            self::SNAKE_CASE, self::TITLE_CASE, self::SCREAMING_SNAKE_CASE =>
            explode('_', strtolower($string)),
            self::KEBAB_CASE,self::TRAIN_CASE, self::SCREAMING_KEBAB_CASE, =>
            explode('-', strtolower($string)),
            self::UNDERSCORE_CAMEL_CASE, self::UNDERSCORE_PASCAL_CASE =>
            array_map(fn($item) => strtolower($item), preg_split("/(?=[$upper])/", lcfirst(ltrim($string, '_')))),
            self::UNDERSCORE_SNAKE_CASE, self::UNDERSCORE_TITLE_CASE, self::UNDERSCORE_SCREAMING_SNAKE_CASE =>
            explode('_', strtolower(ltrim($string, '_'))),
            self::UNDERSCORE_KEBAB_CASE, self::UNDERSCORE_TRAIN_CASE, self::UNDERSCORE_SCREAMING_KEBAB_CASE =>
            explode('-', strtolower(ltrim($string, '_'))),
            default => throw new InvalidArgumentException(sprintf('Unsupported format: %s', $format)),
        };
    }

}
