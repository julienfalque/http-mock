<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Exception;

/**
 * Exception thrown when an argument does not match with expected value.
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
    /**
     * Creates an instance for an invalid argument in a method call.
     *
     * @param object $target
     * @param mixed  $value  the invalid argument
     */
    public static function withExpectedType(
        $target,
        string $method,
        int $argument,
        string $expectedType,
        $value
    ): self {
        if ('object' === $type = \gettype($value)) {
            $type = \get_class($value);
        }

        return new self(sprintf(
            'Method %s::%s() expects argument %d to be %s, %s given.',
            \get_class($target),
            $method,
            $argument,
            $expectedType,
            $type
        ));
    }
}
