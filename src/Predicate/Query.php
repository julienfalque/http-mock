<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against an expected query string.
 *
 * The expected query string can be either the exact value or a
 * {@link http://php.net/manual/en/pcre.pattern.php PCRE pattern}.
 *
 * @see \Jfalque\HttpMock\Predicate\QueryArray
 */
class Query extends PatternPredicate
{
    use NormalizeRequestTrait;

    /**
     * {@inheritdoc}
     */
    protected function getValue(RequestInterface $request): string
    {
        return $this->normalizeRequest($request)->getUri()->getQuery();
    }
}
