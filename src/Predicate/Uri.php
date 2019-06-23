<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against a full URI (scheme, hostname, port number, path, query string and fragment).
 *
 * The expected URI can be either the exact value or a {@link http://php.net/manual/en/pcre.pattern.php PCRE pattern}.
 * With exact value, empty path `""` and absolute path `"/"` are considered equals and match.
 */
class Uri extends PatternPredicate
{
    use NormalizeRequestTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($pattern, $isRegularExpression = false)
    {
        if (!$isRegularExpression) {
            $pattern = preg_replace('~^(\\w+://[^/]+)/([?#].*)?$~', '$1$2', $pattern);
        }

        parent::__construct($pattern, $isRegularExpression);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue(RequestInterface $request): string
    {
        $uri = $this->normalizeRequest($request)->getUri();

        if ('/' === $uri->getPath()) {
            $uri = $uri->withPath('');
        }

        return (string) $uri;
    }
}
