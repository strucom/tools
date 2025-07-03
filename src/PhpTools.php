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

}