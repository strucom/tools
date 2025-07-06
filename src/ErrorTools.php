<?php

namespace Strucom\Tools;

use InvalidArgumentException;
use Throwable;

/**
 * Tools for error handling
 */
class ErrorTools
{
    public const int ERROR_MODE_IGNORE = 0;
    public const int ERROR_MODE_WARNING = 1; // Logs a warning using `trigger_error`.
    public const int ERROR_MODE_EXCEPTION = 2;
    /**
     * Handles exceptions based on the provided error mode.
     *
     * This function processes an exception according to the specified error mode:
     * - `Constants::ERROR_MODE_IGNORE`: Ignores the exception and returns the `$default` value.
     * - `Constants::ERROR_MODE_WARNING`: Logs a warning using `trigger_error` and returns the `$default` value.
     * - `Constants::ERROR_MODE_EXCEPTION`: Re-throws the exception.
     *
     * @param Throwable   $exception The exception to handle.
     * @param int         $errorMode The error handling mode (IGNORE, WARNING, or EXCEPTION).
     * @param mixed       $default   The default value to return for IGNORE or WARNING modes.
     * @param string|null $warning   A custom warning message. If null, only the exception message is used.
     *
     * @return mixed The `$default` value for IGNORE or WARNING modes.
     *
     * @throws Throwable If the error mode is ERROR_MODE_EXCEPTION or an invalid error mode is provided.
     *
     * @since PHP 8.0
     * @author af
     */
    public static function exceptionSwitch(
        Throwable $exception,
        int $errorMode,
        mixed $default = null,
        string|null $warning = null): mixed
    {
        switch ($errorMode) {
            case self::ERROR_MODE_IGNORE:
                return $default;

            case self::ERROR_MODE_WARNING:
                $message = $warning !== null
                    ? sprintf('%s: %s', $warning, $exception->getMessage())
                    : $exception->getMessage();

                trigger_error($message, E_USER_WARNING);
                return $default;

            case self::ERROR_MODE_EXCEPTION:
                throw $exception;

            default:
                throw new InvalidArgumentException(sprintf('Invalid error mode: %d', $errorMode));
        }
    }

}