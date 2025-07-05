<?php

namespace Strucom\Tools;

use InvalidArgumentException;

/**
 * General php functions and constants for CSS code
 */
class CssTools
{
    public const array CSS_FONT_FILE_FORMATS = [
        'woff',      // Web Open Font Format
        'woff2',     // Web Open Font Format 2
        'ttf',       // TrueType Font
        'otf',       // OpenType Font
        'eot',       // Embedded OpenType
        'svg',       // Scalable Vector Graphics
    ];
    public const array CSS_KEYWORDS = [
        // Generic font families
        'serif',
        'sans-serif',
        'monospace',
        'cursive',
        'fantasy',

        // Global values
        'inherit',
        'initial',
        'unset',
        'revert',

        // CSS color keywords
        'transparent',
        'currentColor',

        // CSS display keywords
        'block',
        'inline',
        'inline-block',
        'flex',
        'grid',
        'none',
        'table',
        'table-row',
        'table-cell',

        // Positioning keywords
        'static',
        'relative',
        'absolute',
        'fixed',
        'sticky',

        // Overflow keywords
        'visible',
        'hidden',
        'scroll',
        'auto',
        'clip',

        // Box-sizing keywords
        'border-box',
        'content-box',

        // Other common keywords
        'normal',
        'bold',
        'bolder',
        'lighter',
        'italic',
        'oblique',
        'small-caps',
        'uppercase',
        'lowercase',
        'capitalize',
        'nowrap',
        'pre',
        'pre-wrap',
        'pre-line',
        'break-word',
        'break-all'
    ];
    private const string INDENT = '  ';

    /**
     * Generates a CSS `@font-face` rule based on the provided font data.
     *
     * This function creates a CSS `@font-face` rule using the provided `$fontData` array. It supports
     * specifying the font-family, source files, and additional font properties. The function also
     * handles formatting, such as indentation, spacing, and line breaks, based on the `$withSpace` parameter.
     *
     * @param array  $fontData     An associative array containing font properties (e.g., 'font-family', 'src').
     * @param string $filenameKey  The key in `$fontData` that contains the font file names.
     * @param string $path         The base path to prepend to font file names (optional).
     * @param bool   $withSpace    Whether to format the output with spaces and newlines for readability.
     *
     * @return string The generated CSS `@font-face` rule as a string.
     *
     * @throws InvalidArgumentException If a font file name does not have an extension.
     *
     * @since PHP 8.0
     * @author af
     */
    public static function fontFace(array $fontData, string $filenameKey, string $path = '', bool $withSpace = false): string
    {
        $singleSpace = $withSpace ? ' ' : '';
        $indent = $withSpace ? self::INDENT : '';
        $newLine = $withSpace ? "\n" : '';
        $newLineDoubleIndent = $newLine . $indent . $indent;

        if (!empty($path) && !str_ends_with($path, '/')) {
            $path .= '/';
        }

        $result = [];
        $result[] = '@font-face {';

        foreach ($fontData as $fontDataKey => $fontDataValue) {
            if ((is_string($fontDataValue) && empty($fontDataValue)) || is_null($fontDataValue)) {
                continue;
            }

            switch ($fontDataKey) {
                case 'font-family':
                    if (!is_string($fontDataValue)) {
                        throw new InvalidArgumentException(sprintf('%s value must be a string.', $fontDataKey));
                    }
                    $fontDataValue = trim($fontDataValue, '"\' ');
                    if (!in_array($fontDataValue, self::CSS_KEYWORDS)) {
                        $fontDataValue = '"' . $fontDataValue . '"';
                    }
                    $result[] = 'font-family:' . $singleSpace . $fontDataValue . ';';
                    break;

                case $filenameKey:
                    if (!empty($fontData['src'])) {
                        break;
                    }
                    $src = self::fontSrcFromFilenames($fontDataValue,$path, $newLineDoubleIndent,$singleSpace);

                    if (!empty($src)) {
                        $result[] = $src;
                    }
                    break;

                default:
                    if (!is_scalar($fontDataValue)) {
                        throw new InvalidArgumentException(sprintf('%s value must be scalar.', $fontDataKey));
                    }
                    if (is_bool($fontDataValue)) {
                        $fontDataValue = $fontDataValue ? 'true' : 'false';
                    }
                    $result[] = $fontDataKey . ':' . $singleSpace . $fontDataValue . ';';
            }
        }

        $result = implode($newLine . $indent, $result);
        $result .= $newLine . '}';
        return $result;
    }

    /**
     * Generates the `src` property for a CSS `@font-face` rule from font filenames.
     *
     * This function takes an array or string of font filenames, determines their formats based on file extensions,
     * and generates a properly formatted `src` property for a CSS `@font-face` rule. If a filename does not have
     * an extension or the extension is not a valid font format, an `InvalidArgumentException` is thrown.
     *
     * @param array|string $filenames     The font filenames, either as a single string or an array of strings.
     * @param string       $path          The base path to prepend to each filename.
     * @param string       $newLineIndent The string used for indentation and newlines in the output.
     * @param string       $singleSpace   The string used for spacing in the output.
     *
     * @return string The generated `src` property for the CSS `@font-face` rule.
     *
     * @throws InvalidArgumentException If a filename does not have an extension or the extension is not valid.
     *
     * @since PHP 8.0 (minimum version required for type declarations and union types)
     * @author af
     */
    public static function fontSrcFromFilenames(array|string $filenames, string $path, string $newLineIndent, string $singleSpace): string
    {
        $src = [];
        foreach ((array)$filenames as $filename) {
            if (preg_match('/\.([^.]+)$/', $filename, $matches)) {
                $format = strtolower($matches[1]);

                // Validate the file format
                if (!in_array($format, self::CSS_FONT_FILE_FORMATS, true)) {
                    throw new InvalidArgumentException(
                        sprintf('Invalid font file format: %s. Allowed formats are: %s', $format, implode(', ', self::CSS_FONT_FILE_FORMATS))
                    );
                }

                // Map 'ttf' to 'truetype' for CSS compatibility
                if ($format === 'ttf') {
                    $format = 'truetype';
                }

                $src[] = sprintf('url("%s%s") format("%s")', $path, $filename, $format);
            } else {
                throw new InvalidArgumentException(
                    sprintf('The filename does not have an extension: %s', $filename)
                );
            }
        }

        return empty($src) ? '' : 'src:' . (count($src) > 1 ? $newLineIndent : $singleSpace) . implode(",$newLineIndent", $src) . ';';
    }


}


