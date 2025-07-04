<?php

use PHPUnit\Framework\TestCase;
use Strucom\Tools\PhpTools;

class PhpToolsTest extends TestCase
{
    public function testQuoteImplode(): void
    {
        // Test with default parameters
        $result = PhpTools::quoteImplode(['a', 'b', 'c']);
        self::assertSame('`a`, `b`, `c`', $result);

        // Test with custom glue
        $result = PhpTools::quoteImplode(['a', 'b', 'c'], ' | ');
        self::assertSame('`a` | `b` | `c`', $result);

        // Test with custom quote
        $result = PhpTools::quoteImplode(['a', 'b', 'c'], ', ', '"');
        self::assertSame('"a", "b", "c"', $result);

        // Test with XML tags
        $result = PhpTools::quoteImplode(['a', 'b', 'c'], ', ', '"', 'tag');
        self::assertSame('<tag>"a"</tag>, <tag>"b"</tag>, <tag>"c"</tag>', $result);

        // Test with a single string instead of an array
        $result = PhpTools::quoteImplode('a');
        self::assertSame('`a`', $result);
    }

    public function testSetNestedValue(): void
    {
        $array = [];
        PhpTools::setNestedValue($array, ['level1', 'level2', 'level3'], 'value');
        self::assertSame(['level1' => ['level2' => ['level3' => 'value']]], $array);

        // Test overwriting an existing value
        PhpTools::setNestedValue($array, ['level1', 'level2', 'level3'], 'newValue');
        self::assertSame(['level1' => ['level2' => ['level3' => 'newValue']]], $array);

        // Test adding a new branch
        PhpTools::setNestedValue($array, ['level1', 'level4'], 'anotherValue');
        self::assertSame(
            ['level1' => ['level2' => ['level3' => 'newValue'], 'level4' => 'anotherValue']],
            $array
        );
    }

    public function testPickArrayLeafs(): void
    {
        $array = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
        ];

        PhpTools::pickArrayLeafs($array, 'name');
        self::assertSame(['Alice', 'Bob', 'Charlie'], $array);

        // Test with nested arrays
        $nestedArray = [
            'group1' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
            ],
            'group2' => [
                ['id' => 3, 'name' => 'Charlie'],
            ],
        ];

        PhpTools::pickArrayLeafs($nestedArray, 'name');
        self::assertSame(
            ['group1' => ['Alice', 'Bob'], 'group2' => ['Charlie']],
            $nestedArray
        );
    }

    public function testCreateFlatLookup(): void
    {
        $valueArray = [
            'user' => [
                'name' => 'Alice',
                'age' => 30,
            ],
            'settings' => [
                'theme' => 'dark',
                'notifications' => true,
            ],
        ];

        $keyList = [
            ['user', 'name'],
            ['user', 'age'],
            ['settings', 'theme'],
            ['settings', 'language'], // Non-existent key
        ];

        $result = PhpTools::createFlatLookup($valueArray, $keyList, '.', 'default');
        self::assertSame(
            [
                'user.name' => 'Alice',
                'user.age' => 30,
                'settings.theme' => 'dark',
                'settings.language' => 'default',
            ],
            $result
        );
    }

    public function testGetDomainFromPath(): void
    {
        // Test Plesk structure
        $path = '/var/www/vhosts/example.com/httpdocs';
        $result = PhpTools::getDomainFromPath($path);
        self::assertSame('example.com', $result);

        // Test Apache/Nginx structure
        $path = '/var/www/example.com/public_html';
        $result = PhpTools::getDomainFromPath($path);
        self::assertSame('example.com', $result);

        // Test DirectAdmin structure
        $path = '/home/user/domains/example.com/public_html';
        $result = PhpTools::getDomainFromPath($path);
        self::assertSame('example.com', $result);

        // Test invalid path
        $path = '/random/path/without/domain';
        $result = PhpTools::getDomainFromPath($path);
        self::assertNull($result);
    }

   /* public function testGetDomain(): void
    {
        // todo: test manually

        // Test CLI context
        if (php_sapi_name() === 'cli') {
            $result = PhpTools::getDomain();
            self::assertNotNull($result);
        }

        // Test HTTP context
        $_SERVER['HTTP_HOST'] = 'example.com';
        $result = PhpTools::getDomain();
        self::assertSame('example.com', $result);
    }
*/


    public function testTokenizeString(): void
    {
        // Test with default parameters
        $result = PhpTools::tokenizeString('hello world hello');
        self::assertSame('hello world', $result);

        // Test ignoring case
        $result = PhpTools::tokenizeString('Hello world hello', true);
        self::assertSame('hello world', $result);

        // Test returning as array
        $result = PhpTools::tokenizeString('hello world hello', false, true);
        self::assertSame(['hello', 'world'], $result);

        // Test ignoring case and returning as array
        $result = PhpTools::tokenizeString('Hello world hello', true, true);
        self::assertSame(['hello', 'world'], $result);

        $result = PhpTools::tokenizeString('Hello world hello', false, true);
        self::assertSame(['Hello', 'world', 'hello'], $result);

        // Test with empty input
        $result = PhpTools::tokenizeString('');
        self::assertSame('', $result);

        // Test with input containing only spaces
        $result = PhpTools::tokenizeString('   ');
        self::assertSame('', $result);

        // Test with mixed-case tokens
        $result = PhpTools::tokenizeString('PHP php Php', true);
        self::assertSame('php', $result);

        // Test with special characters
        $result = PhpTools::tokenizeString('hello, world! hello.', true);
        self::assertSame('hello, world! hello.', $result);
    }


    public function testMergeTokenizedString(): void
    {
        // Test merging strings
        $result = PhpTools::mergeTokenizedString(false, 'hello world', 'world hello');
        self::assertSame('hello world', $result);

        // Test merging arrays
        $result = PhpTools::mergeTokenizedString(false, ['hello', 'world'], ['world', 'hello']);
        self::assertSame('hello world', $result);

        // Test returning as array
        $result = PhpTools::mergeTokenizedString(true, 'hello world', ['world', 'hello']);
        self::assertSame(['hello', 'world'], $result);

        // Test invalid input
        $this->expectException(InvalidArgumentException::class);
        PhpTools::mergeTokenizedString(false, ['hello', new stdClass()]);
    }
}
