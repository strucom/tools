<?php

use PHPUnit\Framework\TestCase;
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


public function testValidGenericIdentifier(): void
    {
        $result = XmlTools::html('div', 'Hello World');
        self::assertInstanceOf(DOMElement::class, $result);
        self::assertEquals('div', $result->tagName);
        self::assertEquals('Hello World', $result->textContent);
    }

    public function testInvalidGenericIdentifierHtml(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Invalid HTML generic identifier: invalidTag');
        XmlTools::html('invalidTag');
    }

    public function testIdAttribute(): void
    {
        $result = XmlTools::html('div', null, null, 'unique-id');
        self::assertEquals('unique-id', $result->getAttribute('id'));
    }

    public function testConflictingIdAttribute(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('An element can only have one id attribute');
        XmlTools::html('div', null, null, 'id1', null, null, null, [], ['id' => 'id2']);
    }

    public function testClassAttribute(): void
    {
        $result = XmlTools::html('div', null, ['class1', 'class2']);
        self::assertEquals('class1 class2', $result->getAttribute('class'));
    }

    public function testDataAttributes(): void
    {
        $result = XmlTools::html('div', null, null, null, null, null, null, ['key' => 'value']);
        self::assertEquals('value', $result->getAttribute('data-key'));
    }

    public function testConflictingDataAttributes(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Multiple definitions for attribute "data-key". Use parameter $data instead of $attribute');
        XmlTools::html('div', null, null, null, null, null, null, ['key' => 'value'], ['data-key' => 'conflict']);
    }

    public function testAriaLabel(): void
    {
        $result = XmlTools::html('div', null, null, null, 'Label');
        self::assertEquals('Label', $result->getAttribute('aria-label'));
        self::assertFalse($result->hasAttribute('aria-labelledby'));
        self::assertFalse($result->hasAttribute('aria-hidden'));
    }

    public function testAriaLabelledBy(): void
    {
        $result = XmlTools::html('div', null, null, null, null, 'labelledById');
        self::assertEquals('labelledById', $result->getAttribute('aria-labelledby'));
        self::assertFalse($result->hasAttribute('aria-label'));
        self::assertFalse($result->hasAttribute('aria-hidden'));
    }

    public function testAriaHidden(): void
    {
        $result = XmlTools::html('div', null, null, null, null, null, true);
        self::assertEquals('true', $result->getAttribute('aria-hidden'));
        self::assertFalse($result->hasAttribute('aria-label'));
        self::assertFalse($result->hasAttribute('aria-labelledby'));
    }

    public function testConflictingAriaAttributes(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Multiple definitions for attribute "aria-label". Use parameter $ariaLabel instead of $attribute');
        XmlTools::html('div', null, null, null, 'Label', null, null, [], ['aria-label' => 'Conflict']);
    }

    public function testEmptyContentWithCommentHtml(): void
    {
        $result = XmlTools::html('div', null, null, null, null, null, null, [], [], false, true);
        self::assertEquals('<!-- -->', $result->ownerDocument->saveHTML($result->firstChild));
    }

    public function testWhitespaceHandling(): void
    {
        $result = XmlTools::html('div',
            '   Hello   World   ', null, null, null, null, null, [], [], false, false, XmlTools::WHITESPACE_NORMALIZE);
        self::assertEquals('Hello World', $result->textContent);
    }

    public function testCustomAttributes(): void
    {
        $result = XmlTools::html('div', null, null, null, null, null, null, [], ['custom-attr' => 'value']);
        self::assertEquals('value', $result->getAttribute('custom-attr'));
    }

    public function testReturnAsStringHtml(): void
    {
        $result = XmlTools::html('div', 'Hello World', null, null, null, null, null, [], [], true);
        self::assertIsString($result);
        self::assertStringContainsString('<div>Hello World</div>', $result);
    }
}
