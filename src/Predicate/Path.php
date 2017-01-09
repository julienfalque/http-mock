<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against an expected path.
 *
 * The expected path can be either the exact value or a {@link http://php.net/manual/en/pcre.pattern.php PCRE pattern}.
 * With exact value, empty path `""` and absolute path `"/"` are considered equals and match.
 */
class Path extends PatternPredicate
{
    use NormalizeRequestTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($pattern, $isRegularExpression = false)
    {
        if ('' === $pattern && !$isRegularExpression) {
            $pattern = '/';
        }

        parent::__construct($pattern, $isRegularExpression);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue(RequestInterface $request): string
    {
        if ('' === $path = $this->normalizeRequest($request)->getUri()->getPath()) {
            return '/';
        }

        return $path;
    }
}
