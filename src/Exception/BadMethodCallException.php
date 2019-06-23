<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Exception;

/**
 * Exception thrown when calling an undefined method or some arguments are missing.
 */
class BadMethodCallException extends \BadMethodCallException implements Exception
{
    /**
     * Creates an instance for an undefined method call.
     *
     * @param object $target
     */
    public static function undefinedMethod($target, string $method): self
    {
        return new self(sprintf(
            'Method %s::%s() does not exist.',
            \get_class($target),
            $method
        ));
    }

    /**
     * Creates an instance for a missing argument.
     *
     * @param object $target
     */
    public static function missingArgument($target, string $method, int $index): self
    {
        return new self(sprintf(
            'Missing argument %d for method %s::%s().',
            $index,
            \get_class($target),
            $method
        ));
    }
}
