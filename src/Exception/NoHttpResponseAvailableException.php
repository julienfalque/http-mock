<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Exception;

class NoHttpResponseAvailableException extends \LogicException implements Exception
{
    public static function create(): self
    {
        return new self('No HTTP response available.');
    }
}
