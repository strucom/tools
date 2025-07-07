<?php
namespace Tests;
use DOMElement;
use DOMException;
use PHPUnit\Framework\TestCase;
use Strucom\Tools\HtmlTools;
use Strucom\Tools\XmlTools;

class HtmlToolsTest extends TestCase
{

    public function testValidGenericIdentifier(): void
    {
        $result = HtmlTools::html('div', 'Hello World');
        self::assertInstanceOf(DOMElement::class, $result);
        self::assertEquals('div', $result->tagName);
        self::assertEquals('Hello World', $result->textContent);
    }

    public function testInvalidGenericIdentifierHtml(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Invalid HTML generic identifier: invalidTag');
        HtmlTools::html('invalidTag');
    }

    public function testIdAttribute(): void
    {
        $result = HtmlTools::html('div', null, null, 'unique-id');
        self::assertEquals('unique-id', $result->getAttribute('id'));
    }

    public function testConflictingIdAttribute(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('An element can only have one id attribute');
        HtmlTools::html('div', null, null, 'id1', null, null, null, [], ['id' => 'id2']);
    }

    public function testClassAttribute(): void
    {
        $result = HtmlTools::html('div', null, ['class1', 'class2']);
        self::assertEquals('class1 class2', $result->getAttribute('class'));
    }

    public function testDataAttributes(): void
    {
        $result = HtmlTools::html('div', null, null, null, null, null, null, ['key' => 'value']);
        self::assertEquals('value', $result->getAttribute('data-key'));
    }

    public function testConflictingDataAttributes(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Multiple definitions for attribute "data-key". Use parameter $data instead of $attribute');
        HtmlTools::html('div', null, null, null, null, null, null, ['key' => 'value'], ['data-key' => 'conflict']);
    }

    public function testAriaLabel(): void
    {
        $result = HtmlTools::html('div', null, null, null, 'Label');
        self::assertEquals('Label', $result->getAttribute('aria-label'));
        self::assertFalse($result->hasAttribute('aria-labelledby'));
        self::assertFalse($result->hasAttribute('aria-hidden'));
    }

    public function testAriaLabelledBy(): void
    {
        $result = HtmlTools::html('div', null, null, null, null, 'labelledById');
        self::assertEquals('labelledById', $result->getAttribute('aria-labelledby'));
        self::assertFalse($result->hasAttribute('aria-label'));
        self::assertFalse($result->hasAttribute('aria-hidden'));
    }

    public function testAriaHidden(): void
    {
        $result = HtmlTools::html('div', null, null, null, null, null, true);
        self::assertEquals('true', $result->getAttribute('aria-hidden'));
        self::assertFalse($result->hasAttribute('aria-label'));
        self::assertFalse($result->hasAttribute('aria-labelledby'));
    }

    public function testConflictingAriaAttributes(): void
    {
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Multiple definitions for attribute "aria-label". Use parameter $ariaLabel instead of $attribute');
        HtmlTools::html('div', null, null, null, 'Label', null, null, [], ['aria-label' => 'Conflict']);
    }

    public function testEmptyContentWithCommentHtml(): void
    {
        $result = HtmlTools::html('div', null, null, null, null, null, null, [], [], false, true);
        self::assertEquals('<!-- -->', $result->ownerDocument->saveHTML($result->firstChild));
    }

    public function testWhitespaceHandling(): void
    {
        $result = HtmlTools::html('div',
            '   Hello   World   ', null, null, null, null, null, [], [], false, false, XmlTools::WHITESPACE_NORMALIZE);
        self::assertEquals('Hello World', $result->textContent);
    }

    public function testCustomAttributes(): void
    {
        $result = HtmlTools::html('div', null, null, null, null, null, null, [], ['custom-attr' => 'value']);
        self::assertEquals('value', $result->getAttribute('custom-attr'));
    }

    public function testReturnAsStringHtml(): void
    {
        $result = HtmlTools::html('div', 'Hello World', null, null, null, null, null, [], [], true);
        self::assertIsString($result);
        self::assertStringContainsString('<div>Hello World</div>', $result);
    }
}