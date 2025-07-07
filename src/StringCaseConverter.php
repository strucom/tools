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
    public const string SNAKE_CASE = 'snake_case';
    public const string KEBAB_CASE = 'kebab-case';
    public const string PASCAL_CASE = 'PascalCase';
    public const string SCREAMING_SNAKE_CASE = 'SCREAMING_SNAKE_CASE';
    public const string TITLE_CASE = 'Title_Case';
    public const string ANY_CASE = 'any';

    // Underscore-prefixed constants
    public const string UNDERSCORE_CAMEL_CASE = '_camelCase';
    public const string UNDERSCORE_SNAKE_CASE = '_snake_case';
    public const string UNDERSCORE_KEBAB_CASE = '_kebab-case';
    public const string UNDERSCORE_PASCAL_CASE = '_PascalCase';
    public const string UNDERSCORE_SCREAMING_SNAKE_CASE = '_SCREAMING_SNAKE_CASE';
    public const string UNDERSCORE_TITLE_CASE = '_Title_Case';

// Validation constants (bitmask)
    public const int DO_NOT_VALIDATE   = 0b000000; // 0
    public const int SANITIZE          = 0b000001; // 1
    public const int VALIDATE          = 0b000010; // 2
    public const int ACCEPT_DIGITS     = 0b000100; // 4
    public const int NO_LEADING_DIGITS = 0b001100; // 12 (includes ACCEPT_DIGITS)
    public const int ALLOW_EMPTY       = 0b010000; // 16



    /**
     * Converts a string from one case format to another.
     *
     * @param string $string The input string to be converted.
     * @param string $inFormat The format of the input string (use class constants).
     * @param string $outFormat The desired format of the output string (use class constants).
     * @param int $validateInput Validation mode as a bitmask (e.g., VALIDATE | ACCEPT_DIGITS).
     * @return string The converted string in the desired format.
     *
     * @throws InvalidArgumentException If validation fails and VALIDATE is set in the bitmask.
     * @since PHP 7.0
     * @author af
     */
    public static function convertCase(string $string, string $inFormat = self::ANY_CASE, string $outFormat = self::CAMEL_CASE, int $validateInput = self::SANITIZE): string
    {
        if (!self::isValidCase($string, $inFormat, $validateInput)) {
            if ($string === '') {
                throw new InvalidArgumentException('Input string is empty and bitmask ALLOW_EMPTY is not set.');
            }
            throw new InvalidArgumentException(sprintf('Input string "%s" is not valid for %s.', $string, $inFormat));
        }
        $string = self::sanitizeCase($string, $inFormat, $validateInput);
        $words = self::normalizeToWords($string, $inFormat);
        return self::convertWordsToFormat($words, $outFormat);
    }


    /**
     * Validates the input string for the specified case format.
     *
     * @param string $string The input string.
     * @param string $format The format of the input string.
     * @param int $validateInput Validation mode as a bitmask (e.g., VALIDATE | ACCEPT_DIGITS).
     * @return bool True if the string is valid or bitmask does not include VALIDATE, false otherwise.
     *
     * @throws InvalidArgumentException If an unsupported format is provided.
     * @since PHP 7.0
     * @author af
     */
    public static function isValidCase(string $string, string $format, int $validateInput = self::VALIDATE): bool
    {
        if ($string === '') {
            return ($validateInput & self::ALLOW_EMPTY);
        }
        if (!($validateInput & self::VALIDATE)) {
            return true;
        }
        if (($validateInput & self::NO_LEADING_DIGITS) && preg_match('/^\d',$string)) {
            return false;
        }
        $digitPattern = ($validateInput & self::ACCEPT_DIGITS) ? '0-9' : '';
        return match ($format) {
            self::ANY_CASE => preg_match("/^[a-zA-Z_$digitPattern][a-zA-Z_$digitPattern\-]*$/", $string) === 1,
            self::CAMEL_CASE => preg_match("/^[a-z$digitPattern]+([A-Z][a-z$digitPattern]*)*$/", $string) === 1,
            self::SNAKE_CASE => preg_match("/^[a-z$digitPattern]+(_[a-z$digitPattern]+)*$/", $string) === 1,
            self::KEBAB_CASE => preg_match("/^[a-z$digitPattern]+(-[a-z$digitPattern]+)*$/", $string) === 1,
            self::PASCAL_CASE => preg_match("/^[A-Z][a-z$digitPattern]*([A-Z][a-z$digitPattern]*)*$/", $string) === 1,
            self::SCREAMING_SNAKE_CASE => preg_match("/^[A-Z$digitPattern]+(_[A-Z$digitPattern]+)*$/", $string) === 1,
            self::TITLE_CASE => preg_match("/^[A-Z][a-z$digitPattern]*(_[A-Z][a-z$digitPattern]*)*$/", $string) === 1,
            self::UNDERSCORE_CAMEL_CASE => preg_match("/^_[a-z$digitPattern]+([A-Z][a-z$digitPattern]*)*$/", $string) === 1,
            self::UNDERSCORE_SNAKE_CASE => preg_match("/^_[a-z$digitPattern]+(_[a-z$digitPattern]+)*$/", $string) === 1,
            self::UNDERSCORE_KEBAB_CASE => preg_match("/^_[a-z$digitPattern]+(-[a-z$digitPattern]+)*$/", $string) === 1,
            self::UNDERSCORE_PASCAL_CASE => preg_match("/^_[A-Z][a-z$digitPattern]*([A-Z][a-z$digitPattern]*)*$/", $string) === 1,
            self::UNDERSCORE_SCREAMING_SNAKE_CASE => preg_match("/^_[A-Z$digitPattern]+(_[A-Z$digitPattern]+)*$/", $string) === 1,
            self::UNDERSCORE_TITLE_CASE => preg_match("/^_[A-Z][a-z$digitPattern]*(_[A-Z][a-z$digitPattern]*)*$/", $string) === 1,
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
     * @param string $string The input string to be sanitized.
     * @param string $format The format of the input string (use class constants).
     * @param int $validateInput Validation mode as a bitmask (e.g., VALIDATE | ACCEPT_DIGITS).
     * @return string The sanitized string with offending characters removed.
     *
     * @throws InvalidArgumentException If an unsupported format is provided.
     * @since PHP 7.0
     * @author af
     */
    private static function sanitizeCase(string $string, string $format, int $validateInput = self::SANITIZE): string
    {
        if (!($validateInput & self::SANITIZE)) {
            return $string;
        }
        if (($validateInput & self::NO_LEADING_DIGITS)) {
            $string = ltrim($string, '0123456789');
        }
        $digitPattern = ($validateInput & self::ACCEPT_DIGITS) ? '0-9' : '';
        return match ($format) {
            self::ANY_CASE => preg_replace("/[^a-zA-Z_$digitPattern\-]/", '', $string),
            self::CAMEL_CASE => preg_replace("/[^a-zA-Z$digitPattern]/", '', $string),
            self::UNDERSCORE_CAMEL_CASE => '_' . preg_replace("/[^a-zA-Z$digitPattern]/", '', $string),
            self::SNAKE_CASE, self::UNDERSCORE_SNAKE_CASE => preg_replace("/[^a-z_$digitPattern]/", '', strtolower($string)),
            self::KEBAB_CASE => preg_replace("/[^a-z\-$digitPattern]/", '', strtolower($string)),
            self::UNDERSCORE_KEBAB_CASE => '_' . preg_replace("/[^a-z\-$digitPattern]/", '', strtolower($string)),
            self::PASCAL_CASE => preg_replace("/[^a-zA-Z$digitPattern]/", '', ucfirst($string)),
            self::UNDERSCORE_PASCAL_CASE => '_' . preg_replace("/[^a-zA-Z$digitPattern]/", '', ucfirst($string)),
            self::SCREAMING_SNAKE_CASE, self::UNDERSCORE_SCREAMING_SNAKE_CASE => preg_replace("/[^A-Z_$digitPattern]/", '', strtoupper($string)),
            self::TITLE_CASE, self::UNDERSCORE_TITLE_CASE => preg_replace("/[^a-zA-Z_$digitPattern]/", '', $string),
            default => throw new InvalidArgumentException(sprintf('Unsupported format: %s', $format)),
        };
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
    private static function normalizeToWords(string $string, string $format): array
    {
        return match ($format) {
            self::ANY_CASE => array_map(
                fn($item) => strtolower($item),
                preg_split('/[_\-]|(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $string)
            ),
            self::CAMEL_CASE, self::PASCAL_CASE =>
            array_map(fn($item) => strtolower($item), preg_split('/(?=[A-Z])/', lcfirst($string))),
            self::SNAKE_CASE, self::SCREAMING_SNAKE_CASE, self::TITLE_CASE =>
            explode('_', strtolower($string)),
            self::KEBAB_CASE =>
            explode('-', strtolower($string)),
            self::UNDERSCORE_CAMEL_CASE, self::UNDERSCORE_PASCAL_CASE =>
            array_map(fn($item) => strtolower($item), preg_split('/(?=[A-Z])/', lcfirst(ltrim($string, '_')))),
            self::UNDERSCORE_SNAKE_CASE, self::UNDERSCORE_SCREAMING_SNAKE_CASE, self::UNDERSCORE_TITLE_CASE =>
            explode('_', strtolower(ltrim($string, '_'))),
            self::UNDERSCORE_KEBAB_CASE =>
            explode('-', strtolower(ltrim($string, '_'))),
            default => throw new InvalidArgumentException(sprintf('Unsupported format: %s', $format)),
        };
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
    private static function convertWordsToFormat(array $words, string $format): string
    {
        return match ($format) {
            self::CAMEL_CASE => lcfirst(implode('', array_map(fn($word) => ucfirst(strtolower($word)), $words))),
            self::PASCAL_CASE => implode('', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::SNAKE_CASE => implode('_', array_map(fn($word) => strtolower($word), $words)),
            self::KEBAB_CASE => implode('-', array_map(fn($word) => strtolower($word), $words)),
            self::SCREAMING_SNAKE_CASE => strtoupper(implode('_', $words)),
            self::TITLE_CASE => implode('_', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::UNDERSCORE_CAMEL_CASE => '_' . lcfirst(implode('', array_map(fn($word) => ucfirst(strtolower($word)), $words))),
            self::UNDERSCORE_PASCAL_CASE => '_' . implode('', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            self::UNDERSCORE_SNAKE_CASE => '_' . implode('_', array_map(fn($word) => strtolower($word), $words)),
            self::UNDERSCORE_KEBAB_CASE => '_' . implode('-', array_map(fn($word) => strtolower($word), $words)),
            self::UNDERSCORE_SCREAMING_SNAKE_CASE => '_' . strtoupper(implode('_', $words)),
            self::UNDERSCORE_TITLE_CASE => '_' . implode('_', array_map(fn($word) => ucfirst(strtolower($word)), $words)),
            default => throw new InvalidArgumentException(sprintf('Unsupported format: %s', $format)),
        };
    }
}
