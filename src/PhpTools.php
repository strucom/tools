<?php

namespace Strucom\Tools;

/**
 * General PHP tools
 */
class PhpTools
{

    /**
     * Joins an array of values into a string, wrapping each value with quotes and optionally XML tags.
     *
     * @param array|string $strings  The array of string values.
     * @param string       $glue   The string to use as a separator between values.
     * @param string       $quote  The character(s) to wrap around each value.
     * @param string|null  $tag    An optional XML tag to wrap around each quoted value.
     *
     * @return string The resulting string with quoted and optionally tagged values.
     *
     * @since PHP 7.4
     * @author af
     */
    public static function quoteImplode(array|string $strings, string $glue = ', ', string $quote = '`', ?string $tag = null): string
    {
        if (!empty($tag)) {
            $quoteStart = "<$tag>$quote";
            $quoteEnd = "$quote</$tag>";
        } else {
            $quoteStart = $quote;
            $quoteEnd = $quote;
        }

        return implode(
            $glue,
            array_map(
                static fn($item) => $quoteStart . $item . $quoteEnd,
                (array) $strings
            )
        );
    }

    /**
     * Sets a value in a nested array using the provided keys.
     *
     * This function dynamically creates or navigates through a nested array structure
     * based on the given keys and assigns the specified value to the deepest level.
     *
     * @param array &$valueArray The array to modify. Passed by reference.
     * @param array $keys An array of keys representing the path to the value.
     * @param mixed $value The value to set at the specified path.
     *
     * @since 7.4.0
     * @author af
     */
    public static function setNestedValue(array &$valueArray, array $keys, mixed $value): void
    {
        $current = &$valueArray; // Reference to the root of the array

        // Iterate through all keys except the last one
        foreach (array_slice($keys, 0, -1) as $key) {
            // Create the nested array if it doesn't exist
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }

            // Move the reference deeper into the array
            $current = &$current[$key];
        }

        // Use the last key to set the value
        $lastKey = end($keys);
        $current[$lastKey] = $value;
    }


}