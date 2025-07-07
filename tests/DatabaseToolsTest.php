<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Strucom\Tools\DatabaseTools;
use Strucom\Exception\DatabaseException;

class DatabaseToolsTest extends TestCase
{
    public function testValidSQLName(): void
    {
        // Test a single valid SQL name
        $result = DatabaseTools::validateAndEscapeSQL('table_name');
        self::assertSame('`table_name`', $result);

        // Test a valid SQL name with schema
        $result = DatabaseTools::validateAndEscapeSQL('schema.table_name');
        self::assertSame('`schema`.`table_name`', $result);
    }

    public function testValidSQLNameArray(): void
    {
        // Test an array of valid SQL names
        $result = DatabaseTools::validateAndEscapeSQL(['table1', 'schema.table2']);
        self::assertSame(['`table1`', '`schema`.`table2`'], $result);
    }

    public function testInvalidSQLName(): void
    {
        // Test an invalid SQL name with special characters
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Invalid SQL name component: 'table-name'.");
        DatabaseTools::validateAndEscapeSQL('table-name');
    }

    public function testInvalidSQLNameArray(): void
    {
        // Test an array with one invalid SQL name
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Invalid SQL name component: 'invalid-name'.");
        DatabaseTools::validateAndEscapeSQL(['valid_name', 'invalid-name']);
    }

    public function testInvalidSQLNameArrayNonString(): void
    {
        // Test an array with a non-string element
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('All elements in the names array must be strings. Found: integer.');
        DatabaseTools::validateAndEscapeSQL(['valid_name', 123]);
    }

    public function testEmptySQLName(): void
    {
        // Test an empty SQL name
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Invalid SQL name component: ''.");
        DatabaseTools::validateAndEscapeSQL('');
    }

    public function testEmptySQLNameArray(): void
    {
        // Test an array with an empty SQL name
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Invalid SQL name component: ''.");
        DatabaseTools::validateAndEscapeSQL(['valid_name', '']);
    }

    public function testSQLNameWithDots(): void
    {
        // Test a valid SQL name with multiple dots
        $result = DatabaseTools::validateAndEscapeSQL('schema.subschema.table_name');
        self::assertSame('`schema`.`subschema`.`table_name`', $result);

        // Test an invalid SQL name with invalid components
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Invalid SQL name component: 'invalid-name'.");
        DatabaseTools::validateAndEscapeSQL('schema.invalid-name.table_name');
    }
}
