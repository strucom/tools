<?php

namespace Strucom\Tools;
use DOMElement;
use DOMException;
use InvalidArgumentException;
use Throwable;

/**
 * Tools for handling HTML
 */
class HtmlTools
{
    public const array HTML_ELEMENTS = [
        'a', 'abbr', 'address', 'area', 'article', 'aside', 'audio',
        'b', 'base', 'bdi', 'bdo', 'blockquote', 'body', 'br', 'button',
        'canvas', 'caption', 'cite', 'code', 'col', 'colgroup',
        'data', 'datalist', 'dd', 'del', 'details', 'dfn', 'dialog', 'div', 'dl', 'dt',
        'em', 'embed',
        'fieldset', 'figcaption', 'figure', 'footer', 'form',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hr', 'html',
        'i', 'iframe', 'img', 'input', 'ins',
        'kbd',
        'label', 'legend', 'li', 'link',
        'main', 'map', 'mark', 'meta', 'meter',
        'nav', 'noscript',
        'object', 'ol', 'optgroup', 'option', 'output',
        'p', 'param', 'picture', 'pre', 'progress',
        'q',
        'rb', 'rp', 'rt', 'rtc', 'ruby',
        's', 'samp', 'script', 'section', 'select', 'slot', 'small', 'source', 'span', 'strong', 'style', 'sub', 'summary', 'sup',
        'table', 'tbody', 'td', 'template', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track',
        'u', 'ul',
        'var', 'video',
        'wbr'
    ];
    /**
     * Generates an HTML element using DOMDocument and returns a DOMElement or a string.
     *
     * This function allows you to create an HTML element with optional content, attributes, ARIA attributes, and other properties.
     * It validates the generic identifier (tag name) and ensures proper handling of attributes like `id`, `class`, `data-*`, and ARIA attributes.
     *
     * @param string                       $gi             The generic identifier (tag name) of the HTML element. Defaults to 'div'.
     * @param DOMElement|array|string|float|int|null $content         The content of the HTML element. If null, the element will be self-closing.
     *                                                                If a DOMElement is provided, it will be appended as a child.
     *                                                                If a string, int, or float is provided, it will be added as text content.
     *                                                                If an array is provided, it can contain DOMElements, strings, ints, floats, or nulls, which will be appended in order.
     * @param array|string|null            $class          The class or classes to add to the element. Can be a string or an array of strings.
     * @param string|null                  $id             The ID of the element. Throws an exception if multiple IDs are defined.
     * @param string|null                  $ariaLabel      The ARIA label for the element. Mutually exclusive with `aria-labelledby` and `aria-hidden`.
     * @param string|null                  $ariaLabelledBy The ARIA labelledby attribute. Mutually exclusive with `aria-label` and `aria-hidden`.
     * @param bool|null                    $ariaHidden     The ARIA hidden attribute. Mutually exclusive with `aria-label` and `aria-labelledby`.
     * @param array                        $data           An associative array of `data-*` attributes to add to the element.
     * @param array                        $attributes     Additional attributes for the element. Throws an exception if attributes conflict with other parameters.
     * @param bool                         $asString       Whether to return the element as a string instead of a DOMElement.
     * @param bool                         $commentInEmpty Whether to add an empty comment (`<!-- -->`) if the content is empty.
     * @param int                          $whitespace     A bitmask for whitespace handling: WHITESPACE_REDUCE, WHITESPACE_TRIM, or WHITESPACE_KEEP.
     *
     * @return DOMElement|string The generated HTML element as a DOMElement or a string.
     *
     * @throws DOMException If the generic identifier is invalid, or if conflicting attributes are defined.
     *
     * @since PHP 8.4
     * @author af
     */

    public static function html(
        string $gi = 'div',
        DOMElement|array|string|float|int|null $content = null,
        array|string|null $class = null,
        string|null $id = null,
        string|null $ariaLabel = null,
        string|null $ariaLabelledBy = null,
        bool|null $ariaHidden = null,
        array $data = [],
        array $attributes = [],
        bool $asString = false,
        bool $commentInEmpty = false,
        int $whitespace = XmlTools::WHITESPACE_KEEP,
    ): DOMElement|string {
        if (!in_array($gi, self::HTML_ELEMENTS)) {
            throw new DOMException(sprintf('Invalid HTML generic identifier: %s',  $gi));
        }
        if (!empty($id)) {
            if (!empty($attributes['id']) && $attributes['id'] !== $id) {
                throw new DOMException('An element can only have one id attribute');
            }
            $attributes['id'] = $id;
        }
        if (!empty($class)) {
            $attributes['class'] = PhpTools::mergeTokenizedString( false, $class, $attributes['class'] ?? null);
        }
        foreach ($data as $dataKey => $dataValue) {
            if (isset($attributes['data-' . $dataKey])) {
                throw new DOMException(sprintf(
                        'Multiple definitions for attribute "%s". Use parameter %s instead of $attribute',
                        'data-' . $dataKey,
                        '$data')
                );
            } else {
                $attributes['data-' . $dataKey] = $dataValue;
            }
        }
        if (!empty($ariaLabelledBy)) {
            if (isset($attributes['aria-labelledby'])) {
                throw new DOMException(sprintf(
                        'Multiple definitions for attribute "%s". Use parameter %s instead of $attribute',
                        'aria-labelledby',
                        '$ariaLabelledBy')
                );
            }
            $attributes['aria-labelledby'] = $ariaLabelledBy;
            unset($attributes['aria-label']);
            unset($attributes['aria-hidden']);
        } elseif (!empty($ariaLabel)) {
            if (isset($attributes['aria-label'])) {
                throw new DOMException(sprintf(
                        'Multiple definitions for attribute "%s". Use parameter %s instead of $attribute',
                        'aria-label',
                        '$ariaLabel')
                );
            }
            $attributes['aria-label'] = $ariaLabel;
            unset($attributes['aria-hidden']);
        } elseif (!empty($ariaHidden)) {
            if (isset($attributes['aria-hidden'])) {
                throw new DOMException(sprintf(
                        'Multiple definitions for attribute "%s". Use parameter %s instead of $attribute',
                        'aria-hidden',
                        '$ariaHidden')
                );
            }
            $attributes['aria-hidden'] = 'true';
        }

        return XmlTools::xml(
            gi: $gi,
            content: $content,
            attributes: $attributes,
            asString: $asString,
            commentInEmpty: $commentInEmpty,
            whitespace: $whitespace
        );
    }

    /**
     * Generates a `<link>` element for including a CSS file in the HTML header.
     *
     * If the filename does not contain `.css`, an `InvalidArgumentException` is thrown.
     *
     * @param string $filename The filename of the CSS file to include.
     * @param bool   $asString Whether to return the element as a string (true) or as a DOMElement|null (false).
     * @param int $errorMode The error handling mode (ERROR_MODE_IGNORE, ERROR_MODE_WARNING, or ERROR_MODE_EXCEPTION).
     *
     * @return DOMElement|string|null The generated `<link>` element as a DOMElement or string. On error returns null or ''.
     *
     * @throws InvalidArgumentException If the filename does not contain `.css`.
     * @throws DOMException
     * @throws Throwable (only one of the above exceptions will be thrown as Throwable)
     *
     * @since  PHP 8.0
     * @author af
     */
    public static function cssHeaderLink(
        string $filename,
        bool $asString = false,
        int $errorMode = ErrorTools::ERROR_MODE_EXCEPTION
    ): DOMElement|string|null
    {
        if (!str_contains($filename, '.css')) {
            return ErrorTools::exceptionSwitch(
                exception: new InvalidArgumentException(sprintf('Missing ".css" in filename: %s', $filename)),
                errorMode: $errorMode,
                default: $asString ? '' : null);
        }
        return XmlTools::xml(
            gi: 'link',
            attributes: ['rel' => 'stylesheet', 'type' => 'text/css', 'href' => $filename],
            asString: $asString
        );
    }

    /**
     * Generates a `<script>` element for including a JavaScript file in the HTML header.
     *
     * This function creates a `<script>` element with the appropriate attributes for including a JavaScript file.
     * If the filename does not contain `.js`, an `InvalidArgumentException` is thrown.
     *
     * @param string $filename  The filename of the JavaScript file to include.
     * @param bool   $asString  Whether to return the element as a string (true) or as a DOMElement|null (false).
     * @param int $errorMode The error handling mode (ERROR_MODE_IGNORE, ERROR_MODE_WARNING, or ERROR_MODE_EXCEPTION).
     *
     * @return DOMElement|string|null The generated `<script>` element as a DOMElement or string. On error returns null or ''.
     *
     * @throws InvalidArgumentException If the filename does not contain `.js`.
     * @throws DOMException
     * @throws Throwable (only one of the above exceptions will be thrown as Throwable)
     *
     * @since PHP 8.0
     * @author af
     */
    public static function jsHeaderLink(
        string $filename,
        bool $asString = false,
        int $errorMode = ErrorTools::ERROR_MODE_EXCEPTION
    ): DOMElement|string|null
    {
        if (!str_contains($filename, '.js')) {
            return ErrorTools::exceptionSwitch(
                exception: new InvalidArgumentException(sprintf('Missing ".js" in filename: %s', $filename)),
                errorMode: $errorMode,
                default: $asString ? '' : null);
        }
        return XmlTools::xml(
            gi: 'script',
            attributes: ['src' => $filename],
            asString: $asString,
            commentInEmpty: true
        );
    }


}