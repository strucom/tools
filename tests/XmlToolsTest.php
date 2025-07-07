<?php
namespace Tests;

use DOMDocument;
use DOMElement;
use DOMException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Strucom\Tools\XmlTools;

class XmlToolsTest extends TestCase
{
    public function testValidXmlElement(): void
    {
        $result = XmlTools::xml('div', 'Hello World');
        self::assertInstanceOf(DOMElement::class, $result);
        self::assertEquals('div', $result->tagName);
        self::assertEquals('Hello World', $result->textContent);
    }

    public function testSelfClosingElement(): void
    {
        $result = XmlTools::xml('img', null);
        self::assertInstanceOf(DOMElement::class, $result);
        self::assertEquals('img', $result->tagName);
        self::assertFalse($result->hasChildNodes());
    }

    public function testAttributes(): void
    {
        $result = XmlTools::xml('div', null, ['id' => 'unique-id', 'class' => 'my-class']);
        self::assertEquals('unique-id', $result->getAttribute('id'));
        self::assertEquals('my-class', $result->getAttribute('class'));
    }

    public function testContentArray(): void
    {
        $dom = new DOMDocument();
        $childElement = $dom->createElement('span', 'Child Element');
        $result = XmlTools::xml('div', ['Text Content', $childElement]);
        self::assertEquals('div', $result->tagName);
        self::assertEquals('Text Content', $result->childNodes->item(0)->textContent);
        self::assertEquals('span', $result->childNodes->item(1)->tagName);
        self::assertEquals('Child Element', $result->childNodes->item(1)->textContent);
    }

    public function testInvalidContentType(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage(
            'Invalid content type in content array. Allowed types are DOMElement, string, int, float, or null.'
        );
        XmlTools::xml('div', [new stdClass()]);
    }

    public function testWhitespaceNormalization(): void
    {
        $result = XmlTools::xml('div', "   Hello   World   ", [], false, false, XmlTools::WHITESPACE_NORMALIZE);
        self::assertEquals('Hello World', $result->textContent);
        $result = XmlTools::xml('div', "   Hello   World   ", [], false, false, XmlTools::WHITESPACE_REDUCE);
        self::assertEquals(' Hello World ', $result->textContent);
    }

    public function testWhitespaceTrimming(): void
    {
        $result = XmlTools::xml('div', "   Hello   World   ", [], false, false, XmlTools::WHITESPACE_TRIM);
        self::assertEquals('Hello   World', $result->textContent);
    }

    public function testEmptyContentWithComment(): void
    {
        $result = XmlTools::xml('div', null, [], false, true);
        self::assertEquals('<!-- -->', $result->ownerDocument->saveXML($result->firstChild));
    }

    public function testReturnAsString(): void
    {
        $result = XmlTools::xml('div', 'Hello World', [], true);
        self::assertIsString($result);
        self::assertStringContainsString('<div>Hello World</div>', $result);
    }

    public function testEscapedAttributes(): void
    {
        $result = XmlTools::xml('div', null, ['data-value' => '<script>alert("XSS")</script>']);
        self::assertEquals('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $result->getAttribute('data-value'));
    }

    public function testNestedElements(): void
    {
        $dom = new DOMDocument();
        $childElement = $dom->createElement('span', 'Child Element');
        $result = XmlTools::xml('div', $childElement);
        self::assertEquals('div', $result->tagName);
        self::assertEquals('span', $result->firstChild->tagName);
        self::assertEquals('Child Element', $result->firstChild->textContent);
    }

    public function testMultipleAttributes(): void
    {
        $result = XmlTools::xml('div', null, ['id' => 'test-id', 'class' => 'test-class', 'data-test' => 'value']);
        self::assertEquals('test-id', $result->getAttribute('id'));
        self::assertEquals('test-class', $result->getAttribute('class'));
        self::assertEquals('value', $result->getAttribute('data-test'));
    }

    public function testInvalidAttributeWithSpaces(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Invalid attribute');
        XmlTools::xml('div', null, ['invalid attribute' => 'test-id']);
    }

    public function testInvalidAttributeWithSpecialCharacters(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Invalid attribute');
        XmlTools::xml('div', null, ['$5' => 'test-class']);
    }

    public function testInvalidAttributeStartingWithNumber(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Invalid attribute');
        XmlTools::xml('div', null, ['667' => 'value']);
    }

    public function testEmptyElementWithoutComment(): void
    {
        $result = XmlTools::xml('div', null, [], false, false);
        self::assertFalse($result->hasChildNodes());
    }

    public function testInvalidGenericIdentifier(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Invalid generic identifier');
        XmlTools::xml('', null);
    }


}
