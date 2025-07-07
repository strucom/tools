<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Strucom\Tools\CssTools;
use Strucom\Tools\ErrorTools;
use InvalidArgumentException;

class CssToolsTest extends TestCase
{
    /**
     * Test constants in CssTools.
     */
    public function testConstants(): void
    {
        // Test CSS_FONT_FILE_FORMATS
        $expectedFormats = ['woff', 'woff2', 'ttf', 'otf', 'eot', 'svg'];
        self::assertSame($expectedFormats, CssTools::CSS_FONT_FILE_FORMATS);

        // Test CSS_KEYWORDS
        self::assertContains('serif', CssTools::CSS_KEYWORDS);
        self::assertContains('inherit', CssTools::CSS_KEYWORDS);
        self::assertContains('block', CssTools::CSS_KEYWORDS);
        self::assertContains('bold', CssTools::CSS_KEYWORDS);

        // Test FONT_FACE_DESCRIPTORS
        self::assertContains('font-family', CssTools::FONT_FACE_DESCRIPTORS);
        self::assertContains('src', CssTools::FONT_FACE_DESCRIPTORS);
        self::assertContains('font-style', CssTools::FONT_FACE_DESCRIPTORS);

        // Test CSS_PROPERTIES
        self::assertContains('background', CssTools::CSS_PROPERTIES);
        self::assertContains('border', CssTools::CSS_PROPERTIES);
        self::assertContains('color', CssTools::CSS_PROPERTIES);
        self::assertContains('display', CssTools::CSS_PROPERTIES);
    }

    /**
     * Test the fontFace method.
     */
    public function testFontFace(): void
    {
        $fontData = [
            'font-family' => 'MyFont',
            'src' => 'url("myfont.woff2") format("woff2")',
            'font-weight' => 'bold',
        ];
        $expected = '@font-face {' . "\n" .
            '  font-family: "MyFont";' . "\n" .
            '  src: url("myfont.woff2") format("woff2");' . "\n" .
            '  font-weight: bold;' . "\n" .
            '}';
        $result = CssTools::fontFace($fontData, 'src', '', true);
        self::assertSame($expected, $result);

        // Test with missing src
        $this->expectException(InvalidArgumentException::class);
        CssTools::fontFace(['font-family' => 'MyFont'], 'src', '', true);
    }

    /**
     * Test the validateFontKey method.
     */
    public function testValidateFontKey(): void
    {
        // Valid key
        $result = CssTools::validateFontKey('font-family', ErrorTools::ERROR_MODE_EXCEPTION);
        self::assertSame('font-family', $result);

        // Invalid key with exception
        $this->expectException(InvalidArgumentException::class);
        CssTools::validateFontKey('invalid-key', ErrorTools::ERROR_MODE_EXCEPTION);

        // Invalid key with warning
        $this->expectWarning();
        $result = CssTools::validateFontKey('invalid-key', ErrorTools::ERROR_MODE_WARNING);
        self::assertSame('', $result);

        // Invalid key with ignore
        $result = CssTools::validateFontKey('invalid-key', ErrorTools::ERROR_MODE_IGNORE);
        self::assertSame('', $result);
    }

    /**
     * Test the fontSrcFromFilenames method.
     */
    public function testFontSrcFromFilenames(): void
    {
        $filenames = ['font1.woff', 'font2.ttf'];
        $expected = 'url("font1.woff") format("woff"), ' . "\n" .
            '  url("font2.ttf") format("truetype")';
        $result = CssTools::fontSrcFromFilenames($filenames, '', '  ', ErrorTools::ERROR_MODE_EXCEPTION);
        self::assertSame($expected, $result);

        // Test with invalid filename
        $this->expectException(InvalidArgumentException::class);
        CssTools::fontSrcFromFilenames(['invalidfile'], '', '  ', ErrorTools::ERROR_MODE_EXCEPTION);

        // Test with empty filenames
        $this->expectException(InvalidArgumentException::class);
        CssTools::fontSrcFromFilenames([], '', '  ', ErrorTools::ERROR_MODE_EXCEPTION);
    }

}
