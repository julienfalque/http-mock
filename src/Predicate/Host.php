<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against an expected hostname.
 *
 * The expected hostname can be either the exact value or a
 * {@link http://php.net/manual/en/pcre.pattern.php PCRE pattern}.
 */
class Host extends PatternPredicate
{
    protected function getValue(RequestInterface $request): string
    {
        return $request->getUri()->getHost();
    }
}
