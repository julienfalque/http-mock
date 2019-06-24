<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Exception;

class RequestNormalizationFailedException extends \RuntimeException implements Exception
{
    public static function create(): self
    {
        return new self('Failed normalizing request.');
    }
}
