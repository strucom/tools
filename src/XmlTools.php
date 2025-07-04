<?php

namespace Strucom\Tools;

use DOMDocument;
use DOMElement;
use DOMException;

/**
 * Tools for handling XML
 */
class XmlTools
{
    public const int WHITESPACE_KEEP = 0;
    public const int WHITESPACE_TRIM = 1;
    public const int WHITESPACE_REDUCE = 2;
    public const int WHITESPACE_NORMALIZE = 3;
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
     * Generates an XML element using DOMDocument and returns a DOMElement or a string.
     *
     * @param string                                 $gi             The generic identifier (tag name) of the XML element.
     * @param DOMElement|string|int|float|array|null $content        The content of the XML element. If null, the element will be self-closing.
     *                                                               If a DOMElement is provided, it will be appended as a child.
     *                                                               If a string, int, or float is provided, it will be added as text content.
     *                                                               If an array is provided, it can contain DOMElements, strings, ints, floats, or nulls, which will be appended in order.
     * @param array                                  $attributes     An associative array of attributes for the XML element (key-value pairs).
     * @param bool                                   $asString       Whether to return the element as a string.
     * @param bool                                   $commentInEmpty Add an empty comment with a single space if content is empty.
     * @param int                                    $whitespace     A bitmask for whitespace handling: WHITESPACE_REDUCE, WHITESPACE_TRIM, or WHITESPACE_KEEP.
     * @return DOMElement|string The generated XML element as a DOMElement or string.
     *
     * @throws DOMException If the content array contains invalid items.
     *
     * @since  PHP 8.0.0
     * @author af
     */
    public static function xml(
        string $gi,
        DOMElement|string|int|float|array|null $content = null,
        array $attributes = [],
        bool $asString = false,
        bool $commentInEmpty = false,
        int $whitespace = self::WHITESPACE_KEEP
    ): DOMElement|string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        try {
            $element = $dom->createElement($gi);
        } catch (DOMException $exception) {
            throw new DOMException(sprintf('Invalid generic identifier "%s": %s', $gi, $exception->getMessage()));
        }

        foreach ($attributes as $key => $value) {
            $escapedValue = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');

            if (!$element->setAttribute($key, $escapedValue)) {
                throw new DOMException(sprintf('Invalid attribute "%s" with value "%s"', $key, $escapedValue));
            }
        }

        $content = is_array($content) ? $content : [$content];
        $hasContent = false;

        foreach ($content as $item) {
            if ($item instanceof DOMElement) {
                $importedNode = $dom->importNode($item, true);
                $element->appendChild($importedNode);
                $hasContent = true;
            } elseif (is_string($item) || is_int($item) || is_float($item)) {
                $item = (string)$item;

                if ($whitespace & self::WHITESPACE_TRIM) {
                    $item = trim($item);
                }
                if ($whitespace & self::WHITESPACE_REDUCE) {
                    $item = preg_replace('/\s+/', ' ', $item);
                }

                if ($item !== '') {
                    $escapedContent = htmlspecialchars($item, ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $element->appendChild($dom->createTextNode($escapedContent));
                    $hasContent = true;
                }
            } elseif ($item !== null) {
                throw new DOMException(
                    'Invalid content type in content array. Allowed types are DOMElement, string, int, float, or null.'
                );
            }
        }

        if (!$hasContent && $commentInEmpty) {
            $element->appendChild($dom->createComment(' '));
        }

        return $asString ? $dom->saveXML($element) : $element;
    }
    /**
     * Generates an HTML element using DOMDocument and returns a DOMElement or a string.
     *
     * This function allows you to create an HTML element with optional content, attributes, ARIA attributes, and other properties.
     * It validates the generic identifier (tag name) and ensures proper handling of attributes like `id`, `class`, `data-*`, and ARIA attributes.
     *
     * @param string                       $gi             The generic identifier (tag name) of the HTML element. Defaults to 'div'.
     * @param DOMElement|string|int|float|array|null $content         The content of the HTML element. If null, the element will be self-closing.
     *                                                                If a DOMElement is provided, it will be appended as a child.
     *                                                                If a string, int, or float is provided, it will be added as text content.
     *                                                                If an array is provided, it can contain DOMElements, strings, ints, floats, or nulls, which will be appended in order.
     * @param string|array|null            $class          The class or classes to add to the element. Can be a string or an array of strings.
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
     * @since PHP 8.0.0 (union types introduced)
     * @author af
     */

    public static function html(
        string $gi = 'div',
        DOMElement|string|int|float|array|null $content = null,
        string|array|null $class = null,
        string|null $id = null,
        string|null $ariaLabel = null,
        string|null $ariaLabelledBy = null,
        bool|null $ariaHidden = null,
        array $data = [],
        array $attributes = [],
        bool $asString = false,
        bool $commentInEmpty = false,
        int $whitespace = self::WHITESPACE_KEEP,
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
            $attributes['class'] = PhpTools::mergeTokenizedString(false, $class, $attributes['class'] ?? null);
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

        return self::xml(
            gi: $gi,
            content: $content,
            attributes: $attributes,
            asString: $asString,
            commentInEmpty: $commentInEmpty,
            whitespace: $whitespace
        );
    }


}