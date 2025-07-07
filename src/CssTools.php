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

    public const FONTFACE_DESCRIPTORS = [
        'font-family',          // Specifies the name of the font family
        'src',                  // Specifies the location of the font file(s)
        'font-style',           // Specifies the style of the font (e.g., normal, italic, oblique)
        'font-weight',          // Specifies the weight (thickness) of the font
        'font-stretch',         // Specifies how condensed or expanded the font is
        'font-display',         // Specifies how the font is displayed while loading
        'unicode-range',        // Specifies the range of Unicode code points to use from the font
        'font-feature-settings',// Allows control over advanced typographic features in OpenType fonts
        'font-variation-settings', // Allows control over variable fonts by specifying axis names and values
        'ascent-override',      // Overrides the ascent metric of the font
        'descent-override',     // Overrides the descent metric of the font
        'line-gap-override',    // Overrides the line gap metric of the font
        'size-adjust',          // Defines a multiplier for scaling glyph outlines and metrics
    ];
    public const CSS_PROPERTIES = [
        // Background properties
        'background',
        'background-attachment',
        'background-blend-mode',
        'background-clip',
        'background-color',
        'background-image',
        'background-origin',
        'background-position',
        'background-repeat',
        'background-size',

        // Border and outline properties
        'border',
        'border-bottom',
        'border-bottom-color',
        'border-bottom-left-radius',
        'border-bottom-right-radius',
        'border-bottom-style',
        'border-bottom-width',
        'border-collapse',
        'border-color',
        'border-image',
        'border-image-outset',
        'border-image-repeat',
        'border-image-slice',
        'border-image-source',
        'border-image-width',
        'border-left',
        'border-left-color',
        'border-left-style',
        'border-left-width',
        'border-radius',
        'border-right',
        'border-right-color',
        'border-right-style',
        'border-right-width',
        'border-spacing',
        'border-style',
        'border-top',
        'border-top-color',
        'border-top-left-radius',
        'border-top-right-radius',
        'border-top-style',
        'border-top-width',
        'border-width',
        'outline',
        'outline-color',
        'outline-offset',
        'outline-style',
        'outline-width',

        // Box model properties
        'box-shadow',
        'box-sizing',

        // Color properties
        'color',
        'opacity',

        // Display and visibility properties
        'display',
        'visibility',
        'z-index',

        // Flexbox properties
        'align-content',
        'align-items',
        'align-self',
        'flex',
        'flex-basis',
        'flex-direction',
        'flex-flow',
        'flex-grow',
        'flex-shrink',
        'flex-wrap',
        'justify-content',
        'order',

        // Grid properties
        'grid',
        'grid-area',
        'grid-auto-columns',
        'grid-auto-flow',
        'grid-auto-rows',
        'grid-column',
        'grid-column-end',
        'grid-column-gap',
        'grid-column-start',
        'grid-gap',
        'grid-row',
        'grid-row-end',
        'grid-row-gap',
        'grid-row-start',
        'grid-template',
        'grid-template-areas',
        'grid-template-columns',
        'grid-template-rows',

        // Margin and padding properties
        'margin',
        'margin-bottom',
        'margin-left',
        'margin-right',
        'margin-top',
        'padding',
        'padding-bottom',
        'padding-left',
        'padding-right',
        'padding-top',

        // Positioning properties
        'bottom',
        'clear',
        'clip',
        'float',
        'left',
        'position',
        'right',
        'top',

        // Table properties
        'border-collapse',
        'border-spacing',
        'caption-side',
        'empty-cells',
        'table-layout',

        // Text and typography properties
        'direction',
        'font',
        'font-family',
        'font-feature-settings',
        'font-kerning',
        'font-optical-sizing',
        'font-size',
        'font-size-adjust',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-variant-alternates',
        'font-variant-caps',
        'font-variant-east-asian',
        'font-variant-ligatures',
        'font-variant-numeric',
        'font-variant-position',
        'font-weight',
        'letter-spacing',
        'line-height',
        'quotes',
        'text-align',
        'text-align-last',
        'text-combine-upright',
        'text-decoration',
        'text-decoration-color',
        'text-decoration-line',
        'text-decoration-style',
        'text-indent',
        'text-justify',
        'text-overflow',
        'text-shadow',
        'text-transform',
        'unicode-bidi',
        'vertical-align',
        'white-space',
        'word-break',
        'word-spacing',
        'word-wrap',
        'writing-mode',

        // Animation properties
        'animation',
        'animation-delay',
        'animation-direction',
        'animation-duration',
        'animation-fill-mode',
        'animation-iteration-count',
        'animation-name',
        'animation-play-state',
        'animation-timing-function',

        // Transition properties
        'transition',
        'transition-delay',
        'transition-duration',
        'transition-property',
        'transition-timing-function',

        // Transform properties
        'transform',
        'transform-origin',
        'transform-style',

        // Miscellaneous properties
        'all',
        'content',
        'cursor',
        'filter',
        'resize',
        'scroll-behavior',
        'will-change',
        'clip-path',
        'perspective',
        'perspective-origin',
        'backface-visibility',
        'overflow',
        'overflow-x',
        'overflow-y',
        'user-select'
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
     * @throws InvalidArgumentException If a font file name does not have an extension or $fontData[$filenameKey] amd $fontData['src'] are both empty
     *
     * @since PHP 8.0
     * @author af
     */
    public static function fontFace(array $fontData, string $filenameKey, string $path = '', bool $withSpace = false): string
    {
        if (empty($fontData[$filenameKey]) && empty($fontData['src'])) {
            throw new InvalidArgumentException(sprintf('$fontData["src"] and $fontData[%s] are both empty.', $filenameKey));
        }
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
                    $result[] = self::fontFamily(key: $fontDataKey, value: $fontDataValue, singleSpace: $singleSpace);
                    break;

                case $filenameKey:
                    if (!empty($fontData['src'])) {
                        break;
                    }
                    $src = self::fontSrcFromFilenames(
                        filenames: $fontDataValue,
                        path: $path,
                        newLineIndent: $newLineDoubleIndent,
                        singleSpace: $singleSpace);

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
     * Generates a CSS `font-family` declaration from the provided key and value.
     *
     * This function validates that the `$value` is a string, trims any surrounding quotes or whitespace,
     * and ensures that the value is properly quoted unless it matches a predefined CSS keyword.
     *
     * @param string $key         The key associated with the font-family value (used for error messages).
     * @param mixed  $value       The value to be used for the `font-family` declaration.
     * @param string $singleSpace A string representing a single space for formatting the output.
     *
     * @return string The generated CSS `font-family` declaration.
     *
     * @throws InvalidArgumentException If the `$value` is not a string.
     *
     * @since PHP 8.0 (minimum version required for type declarations and named arguments)
     * @author af
     */
    private static function fontFamily(string $key, mixed $value, string $singleSpace): string {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('%s value must be a string.', $key));
        }
        $value = trim($value, '"\' ');
        if (!in_array($value, self::CSS_KEYWORDS)) {
            $value = '"' . $value . '"';
        }
        return 'font-family:' . $singleSpace . $value . ';';
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


