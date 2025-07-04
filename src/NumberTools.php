<?php

namespace Strucom\Tools;

use InvalidArgumentException;

/**
 * Tools for manipulating numeric data
 */
class NumberTools
{
    private const string DIGIT_LOWER_UPPER = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Convert an integer to another base.
     *
     * @param int      $number The number to convert.
     * @param int|null $base   If a value is given, use the given number of characters from the start of $digits.
     * @param string   $digits The character set to be used for the conversion.
     *
     * @return string  The representation of the number in the specified base without any prefix or suffix indicating the base.
     *
     * @throws InvalidArgumentException If the base is invalid or the digits string is empty or shorter than the given base.
     *
     * @since PHP 7.1
     * @author af
     */

    public static function intBaseConvert(int $number, int|null $base = null, string $digits = self::DIGIT_LOWER_UPPER): string
    {
        if (empty($digits)) {
           throw new InvalidArgumentException('Digits must be a non-empty string');
        }
        if ($base === null) {
            $base = strlen($digits);
        } elseif ($base > strlen($digits)) {
            throw new InvalidArgumentException('Base cannot be greater than the number of digits provided.');
        } elseif ($base <= 0) {
            throw new InvalidArgumentException('Base must be greater than 0.');
        }

        if ($number === 0) {
            return $digits[0];
        }

        $sign = ($number < 0) ? '-' : '';
        $number = abs($number);

        $result = '';

        while ($number > 0) {
            $remainder = $number % $base; // Get the remainder
            $result = $digits[$remainder] . $result; // Prepend the corresponding character
            $number = intdiv((int)$number, $base); // Divide the number by the base
        }

        return $sign . $result;
    }

}