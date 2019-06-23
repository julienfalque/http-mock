<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against an expected fragment.
 *
 * The expected fragment can be either the exact value or a
 * {@link http://php.net/manual/en/pcre.pattern.php PCRE pattern}.
 */
class Fragment extends PatternPredicate
{
    protected function getValue(RequestInterface $request): string
    {
        return $request->getUri()->getFragment();
    }
}
