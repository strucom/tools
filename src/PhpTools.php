<?php

namespace Strucom\Tools;


use InvalidArgumentException;

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

    /**
     * Modifies a nested array by replacing subarrays containing a specific key ($leafKey)
     * with the value of that key.
     *
     * @param array &$array   The array to modify. Passed by reference.
     * @param string $leafKey The key to look for in subarrays.
     *
     * @since 7.0.0
     * @author af
     */
    public static function pickArrayLeafs(array &$array, string $leafKey): void
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                // If the subarray contains $leafKey, replace it with the value of $leafKey
                if (array_key_exists($leafKey, $value)) {
                    $array[$key] = $value[$leafKey];
                } else {
                    // Otherwise, recursively process the subarray
                    self::pickArrayLeafs(array: $value, leafKey: $leafKey);
                }
            }
        }
    }

    /**
     * Creates a flat lookup array from a nested value array.
     *
     * For each set of keys in $keyList, a flat key is created by imploding the keys with $keySeparator.
     * The value for the flat key is retrieved from $valueArray using the keys. If the value is not set,
     * the $defaultValue is used.
     *
     * @param array $valueArray The nested array to retrieve values from.
     * @param array $keyList A list of key arrays to look up in the $valueArray.
     * @param string $keySeparator The separator used to create flat keys.
     * @param mixed $defaultValue The default value to use if a key path is not found in $valueArray.
     * @return array The resulting flat lookup array.
     *
     * @since 8.0.0
     * @author af
     */
    public static function createFlatLookup(array $valueArray, array $keyList, string $keySeparator, mixed $defaultValue): array
    {
        $result = [];

        foreach ($keyList as $keys) {
            $flatKey = implode($keySeparator, $keys);

            // Retrieve the value from the nested array using the keys
            $value = $valueArray;
            foreach ($keys as $key) {
                if (is_array($value) && array_key_exists($key, $value)) {
                    $value = $value[$key];
                } else {
                    // If the key path is not found, use the default value
                    $value = $defaultValue;
                    break;
                }
            }

            $result[$flatKey] = $value;
        }

        return $result;
    }

    /**
     * Extracts the domain name from a given path based on common directory structures.
     *
     * This function checks for common hosting directory structures such as
     * - Plesk: /var/www/vhosts/DOMAIN_NAME/...
     * - Apache/Nginx: /var/www/DOMAIN_NAME/...
     * - DirectAdmin: /home/USERNAME/domains/DOMAIN_NAME/...
     *
     * @param string $path The directory path to analyze.
     * @return string|null The extracted domain name, or null if it cannot be determined.
     *
     * @since 7.0.0
     * @author af
     */
    public static function getDomainFromPath(string $path): string|null
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);

        // Check for Multi-Domain Hosting structure (Plesk)
        if (in_array('vhosts', $pathParts)) {
            $index = array_search('vhosts', $pathParts);
            return $pathParts[$index + 1] ?? null;
        }

        // Check for Apache/Nginx structure
        if (in_array('www', $pathParts)) {
            $index = array_search('www', $pathParts);
            return $pathParts[$index + 1] ?? null;
        }

        // Check for DirectAdmin structure
        if (in_array('domains', $pathParts)) {
            $index = array_search('domains', $pathParts);
            return $pathParts[$index + 1] ?? null;
        }

        // Return null if no domain can be determined
        return null;
    }

    /**
     * Determines the domain name based on the execution context (CLI or HTTP).
     *
     * If the script is executed via CLI, it uses the `getDomainFromPath` function to extract
     * the domain from the directory path. If the script is executed via HTTP, it retrieves
     * the domain from the HTTP_HOST server variable.
     *
     * @return string|null The domain name, or null if it cannot be determined.
     *
     * @since 7.0.0
     * @author af
     */
    public static function getDomain(): string|null
    {
        if (php_sapi_name() === 'cli') {
            return self::getDomainFromPath(__DIR__);
        } else {
            // Script is called via HTTP
            return $_SERVER['HTTP_HOST'] ?? null;
        }
    }
    /**
     * Normalize a string of tokens to return an array or a string of unique tokens separated by a single space.
     *
     * @param string $input The input string containing tokens.
     * @param bool   $ignoreCase Ignore the case when filtering for unique tokens.
     * @param bool   $asArray return tokens as an array
     *
     * @return string|array An array or a string of unique tokens separated by a single space.
     *
     * @since PHP 7.4.0
     * @author af
     */
    public static function tokenizeString(string $input, bool $ignoreCase = false, bool $asArray = false): string|array
    {
        $words = preg_split(pattern: '/\s+/', subject: $input, flags: PREG_SPLIT_NO_EMPTY);

        if ($ignoreCase) {
            $lowercaseWords = array_map(fn($word) => strtolower($word), $words);
            $uniqueWords = array_unique($lowercaseWords);
        } else {
            $uniqueWords = array_unique($words);
        }

        return $asArray ? $uniqueWords : implode(' ', $uniqueWords);
    }
    /**
     * Merges tokenized strings into unique tokens.
     *
     * This function accepts a variable number of arguments, each of which can be an array, a string, or null.
     * It merges all the arguments into a single string, with each argument separated by a space.
     * Duplicate tokens are removed, so the final result only contains unique tokens.
     * If an argument is an array, each element is treated as a token.
     * If an argument is a string, it is split into tokens by space.
     * If an argument is null, it is ignored.
     *
     * @param bool $asArray If true, return an array. Otherwise, return a string.
     * @param array|string|null ...$tokens The tokens to be merged.
     * @return string|array The merged tokenized array or string with unique tokens.
     *
     * @throws InvalidArgumentException If an array contains non-scalar elements.
     *
     * @since PHP 8.4.0
     * @author af
     */
    public static function mergeTokenizedString(bool $asArray, array|string|null ...$tokens): string|array
    {
        $allTokens = [];

        foreach ($tokens as $token) {
            if (is_array($token)) {
                // Check if any element in the array is not scalar
                if (array_any($token, fn($item) => !is_scalar($item))) {
                    throw new InvalidArgumentException('All elements in the token array must be scalar values.');
                }
                $allTokens = array_merge($allTokens, $token);
            } elseif (is_string($token) && trim($token) !== '') {
                $allTokens = array_merge($allTokens, explode(' ', $token));
            }
        }

        return self::tokenizeString(implode(' ', $allTokens), asArray: $asArray);
    }



}