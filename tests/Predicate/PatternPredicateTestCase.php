<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Exception\InvalidArgumentException;
use Jfalque\HttpMock\Predicate\PatternPredicate;
use PHPUnit\Framework\TestCase;

/**
 * Base class for {@see PatternPredicate} tests.
 */
class PatternPredicateTestCase extends TestCase
{
    protected function doInvalidPatternTest(PatternPredicate $predicate): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The regular expression pattern "foo" is invalid: Delimiter must not be alphanumeric or backslash.');

        $predicate(new Request('GET', 'http://foo'));
    }
}
