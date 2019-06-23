<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against an expected body.
 *
 * The expected body can be either the exact contents or a
 * {@link http://php.net/manual/en/pcre.pattern.php PCRE pattern}.
 */
class Body extends PatternPredicate
{
    protected function getValue(RequestInterface $request): string
    {
        return (string) $request->getBody();
    }
}
