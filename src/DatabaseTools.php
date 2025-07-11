<?php

namespace Strucom\Tools;


use InvalidArgumentException;
use PDO;
use PDOException;
use Strucom\Exception\DatabaseException;

/**
 * Tools for handling SQL databases
 */
class DatabaseTools
{
    private const string VALID_SQL_NAME_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]+$/';

    /**
     * Return a comma-separated SQL placeholder string with a `?` for each value. Optionally add brackets.
     *
     * @param array $values The array of values for which placeholders are generated.
     * @param bool  $raw    If true, return only the placeholder string. If false, wrap the placeholder string in brackets.
     *
     * @return string The generated placeholder string.
     *
     * @throws InvalidArgumentException If the $values array is empty.
     *
     * @since PHP 7.0
     * @author af
     */
    public static function getPlaceholder(array $values, bool $raw = false): string
    {
        if (count($values) === 0) {
            throw new InvalidArgumentException('Placeholder array must have at least one element.');
        }
        $placeholder = implode(', ', array_fill(0, count($values), '?'));
        return $raw ? $placeholder : "($placeholder)";
    }
    /**
     * Filter given database names against existing databases in the server.
     *
     * @param PDO        $pdo           The PDO connection object.
     * @param array|null $databaseNames The array of database names to be filtered.
     *                                  If null or empty, return all database names.
     *
     * @return array The array of existing database names in lowercase.
     *
     * @throws PDOException If the query fails.
     *
     * @internal SQL
     *
     * @since PHP 7.1
     * @author af
     */
    public static function filterDatabaseNames(PDO $pdo, ?array $databaseNames = null): array
    {
        $stmt = $pdo->query('SHOW DATABASES');

        $allDatabaseNames = array_map(static fn($name) => strtolower($name), $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (empty($databaseNames)) {
            return $allDatabaseNames;
        }

        return array_intersect(
            array_map(static fn($name) => strtolower($name), $databaseNames),
            $allDatabaseNames
        );
    }

    /**
     * Filter table names against existing tables in the database.
     *
     * @param PDO        $pdo         The PDO connection object.
     * @param array|null $tableNames  The array of table names to be filtered.
     *                                If null or empty, return all table names.
     * @param bool       $anyDatabase If true, checks against all databases in the INFORMATION_SCHEMA.
     *                                If false, checks against the current PDO database.
     *
     * @return array The array of filtered table names in lowercase.
     *
     * @throws PDOException If the query fails.
     *
     * @internal SQL
     *
     * @since PHP 7.1
     * @author af
     */
    public static function filterTableNames(PDO $pdo, ?array $tableNames = null, bool $anyDatabase = false): array
    {
        $sqlColumnTableName = 'TABLE_NAME'; // Used to avoid PHPStorm code inspection warnings

        $query = !$anyDatabase
            ? 'SHOW TABLES'
            : "SELECT $sqlColumnTableName FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA != 'information_schema'";

        $stmt = $pdo->query($query);

        $allTableNames = array_map(static fn($name) => strtolower($name), $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (empty($tableNames)) {
            return $allTableNames;
        }
        return array_intersect(
            array_map(static fn($name) => strtolower($name), $tableNames),
            $allTableNames
        );
    }
    /**
     * Filter column names against existing columns in the specified tables.
     *
     * @param PDO        $pdo         The PDO connection object.
     * @param array|null $columnNames The array of column names to be filtered.
     *                                If null or empty, return all column names.
     * @param array|null $tableNames  The array of table names to check for column names.
     *                                If null or empty, use all tables.
     * @param bool       $intersect   If true, return only column names that occur in all tables.
     *                                If false, return column names that occur in at least one table.
     * @param bool       $anyDatabase If true, check against all databases in the INFORMATION_SCHEMA.
     *                                If false, check against the current PDO database.
     *
     * @return array The array of filtered column names in lowercase.
     *
     * @throws PDOException If the query fails.
     * @throws InvalidArgumentException If the $tableNames array contains non-string values.
     *
     * @internal SQL
     *
     * @since PHP 7.1
     * @author af
     */
    public static function filterColumnNames(
        PDO $pdo,
        ?array $columnNames = null,
        ?array $tableNames = null,
        bool $intersect = false,
        bool $anyDatabase = false
    ): array {
        // Validate $tableNames if provided
        if (!empty($tableNames)) {
            $filteredTableNames = array_filter($tableNames, 'is_string');
            if (count($filteredTableNames) !== count($tableNames)) {
                throw new InvalidArgumentException('The $tableNames array must only contain string values.');
            }
            $tableNamesList = 'AND TABLE_NAME IN ' . self::getPlaceholder($tableNames);
        } else {
            $tableNamesList = '';
        }

        $databaseCondition = $anyDatabase
            ? "!= 'information_schema'"
            : '= (SELECT DATABASE())';

        $queryCondition = 'TABLE_SCHEMA ' . $databaseCondition . $tableNamesList;

        $query = "SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE $queryCondition";

        // Add grouping and filtering if in intersect mode
        if ($intersect) {
            $query .= " GROUP BY COLUMN_NAME HAVING COUNT(DISTINCT TABLE_NAME) = 
          (SELECT COUNT(DISTINCT TABLE_NAME) FROM INFORMATION_SCHEMA.COLUMNS WHERE $queryCondition)";
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($tableNames);


        $allColumnNames = array_map(static fn($name) => strtolower($name), $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (empty($columnNames)) {
            return $allColumnNames;
        }

        return array_intersect(
            array_map(static fn($name) => strtolower($name), $columnNames),
            $allColumnNames
        );
    }
    /**
     * Retrieves a key-value pair array from a database table.
     *
     * @param PDO    $pdo                The PDO instance for database interaction.
     * @param string $tableName          The name of the table to query.
     * @param string $keyColumn          The column to use as the key in the resulting array.
     * @param string $valueColumn        The column to use as the value in the resulting array.
     * @param bool   $throwDuplicateKeys Whether to throw an exception if duplicate keys are found. Otherwise, it will use the last occurrence of each key.
     * @return array The resulting key-value pair array.
     *
     * @throws PDOException If the query fails
     * @throws DatabaseException If duplicate keys are found (when `$throwDuplicateKeys` is true).
     *
     * @since PHP 8.0
     * @author af
     */
    public static function getLookupArray(PDO $pdo, string $tableName, string $keyColumn, string $valueColumn, bool $throwDuplicateKeys = false): array
    {
        $stmt = $pdo->prepare(
            sprintf(
                'SELECT %s, %s FROM %s',
                self::validateAndEscapeSQL($keyColumn),
                self::validateAndEscapeSQL($valueColumn),
                self::validateAndEscapeSQL($tableName)
            )
        );
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $rowCount = $stmt->rowCount();
        if ((count($result) !== $rowCount) && $throwDuplicateKeys) {
            throw new DatabaseException(sprintf('Duplicate value found for key column: %s in table %s', $keyColumn,$tableName));
        }
        return $result;
    }

    /**
     * Validates and escapes SQL table or column names.
     *
     * This function checks if the provided names are valid SQL identifiers. Names can include schemas (e.g., `schema.table`).
     * It ensures that each component of the name is properly escaped with backticks unless already escaped.
     * If a name is invalid, it throws a DatabaseException.
     *
     * @param array|string $name The name(s) to validate and sanitize. Can be a string or an array of strings.
     * @return array|string The validated name(s)
     * @throws DatabaseException If a name is not valid.
     *
     * @since PHP 8.0
     * @author af
     */
    public static function validateAndEscapeSQL(array|string $name): array|string
    {
        if (is_array($name)) {
            foreach ($name as &$item) {
                if (!is_string($item)) {
                    throw new DatabaseException(
                        sprintf('All elements in the names array must be strings. Found: %s.', gettype($item))
                    );
                }
                $item = self::validateAndEscapeSQL($item);
            }
            return $name;
        }

        $components = explode('.', $name);

        foreach ($components as &$component) {
            if (str_starts_with($component, '`') && str_ends_with($component, '`')) {
                $component = trim($component, '`');
            }
            if (!preg_match(self::VALID_SQL_NAME_PATTERN, $component)) {
                throw new DatabaseException(sprintf("Invalid SQL name component: '%s'.", $component));
            }

            $component = '`' . $component . '`';
        }

        return implode('.', $components);
    }


}