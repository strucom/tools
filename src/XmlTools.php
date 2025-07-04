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
    public const int WHITESPACE_NORMALIZE = 2;
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
     * @param int                                    $whitespace     A bitmask for whitespace handling: WHITESPACE_NORMALIZE, WHITESPACE_TRIM, or WHITESPACE_KEEP.
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
        $element = $dom->createElement($gi);

        foreach ($attributes as $key => $value) {
            $escapedValue = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $element->setAttribute($key, $escapedValue);
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
                if ($whitespace & self::WHITESPACE_NORMALIZE) {
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
            throw new DOMException('Invalid HTML generic identifier: ' . $gi);
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
                throw new DOMException(
                    'Multiple definitions for data attribute data-' . $dataKey . "\n" .
                    'Use parameter $data instead of $attribute to define data attributes'
                );
            } else {
                $attributes['data-' . $dataKey] = $dataValue;
            }
        }
        if (!empty($ariaLabelledBy)) {
            if (isset($attributes['aria-labelledby'])) {
                throw new DOMException(
                    'Multiple definitions for data attribute "aria-labelledby"' . "\n" .
                    'Use parameter $ariaLabelledBy instead of $attribute to define data aria attribute'
                );
            }
            $attributes['aria-labelledby'] = $ariaLabelledBy;
            unset($attributes['aria-label']);
            unset($attributes['aria-hidden']);
        } elseif (!empty($ariaLabel)) {
            if (isset($attributes['aria-label'])) {
                throw new DOMException(
                    'Multiple definitions for data attribute "aria-label"' . "\n" .
                    'Use parameter $ariaLabel instead of $attribute to define data aria attribute'
                );
            }
            $attributes['aria-label'] = $ariaLabel;
            unset($attributes['aria-hidden']);
        } elseif (!empty($ariaHidden)) {
            if (isset($attributes['aria-hidden'])) {
                throw new DOMException(
                    'Multiple definitions for data attribute "aria-hidden"' . "\n" .
                    'Use parameter $ariaHidden instead of $attribute to define data aria attribute'
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