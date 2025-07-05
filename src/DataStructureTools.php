<?php

namespace Strucom\Tools;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Strucom\Exception\NotFoundException;

/**
 * A utility class for working with data structures.
 */
class DataStructureTools
{
    /**
     * Retrieves a value from an array or a PSR-11 ContainerInterface.
     *
     * @param array|ContainerInterface $configData The configuration data, either as an array or a container.
     * @param string                   $key        The key to retrieve from the configuration data.
     *
     * @return mixed The value associated with the key.
     *
     * @throws NotFoundException If the key is not found in the configuration data.
     *
     * @since PHP 8.0
     * @author af
     */
    public static function getArrayOrContainerValue(array|ContainerInterface $configData, string $key): mixed
    {
        if (!self::hasArrayOrContainerKey($configData, $key)) {
            throw new NotFoundException(sprintf('Missing entry in configData for %s', $key));
        }
        if (is_array($configData)) {
            return $configData[$key];
        } else {
            try {
                return $configData->get($key);
            } catch (ContainerExceptionInterface $exception) {
                // Should never happen as we checked with has()
                throw new NotFoundException(
                    sprintf('Missing entry in configData for %s', $key),
                    $exception->getCode(),
                    $exception // Pass the original exception as the previous exception
                );
            }
        }
    }

    /**
     * Checks for a key in an array or a PSR-11 ContainerInterface.
     *
     * @param ContainerInterface|array $configData The configuration data, either as an array or a container.
     * @param string                   $key        The key to check.
     *
     * @return bool
     *
     * @throws InvalidArgumentException If the key is not found in the configuration data.
     *
     * @since PHP 8.0
     * @author af
     */
    public static function hasArrayOrContainerKey(ContainerInterface|array $configData, string $key): bool
    {
        if (is_array($configData)) {
            return isset($configData[$key]);
        } else {
            return $configData->has($key);
        }
    }
}

