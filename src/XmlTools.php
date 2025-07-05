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

    public const string XML_ATTRIBUTE_NAME_PATTERN = '/^[a-zA-Z_][\w.\-:]*$/';

    /**
     * Generates an XML element using DOMDocument and returns a DOMElement or a string.
     *
     * @param string                                 $gi             The generic identifier (tag name) of the XML element.
     * @param DOMElement|array|string|float|int|null $content        The content of the XML element. If null, the element will be self-closing.
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
     * @since  PHP 8.0
     * @author af
     */
    public static function xml(
        string $gi,
        DOMElement|array|string|float|int|null $content = null,
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
            if (!preg_match(self::XML_ATTRIBUTE_NAME_PATTERN, $key)) {
                throw new DOMException(sprintf('Invalid attribute "%s"', $key));
            }
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

}