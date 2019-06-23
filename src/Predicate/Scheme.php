<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against a case-insensitive scheme.
 */
class Scheme implements Predicate
{
    /**
     * @var string
     */
    private $scheme;

    /**
     * Constructor.
     */
    public function __construct(string $scheme)
    {
        $this->scheme = strtolower($scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RequestInterface $request): bool
    {
        return $request->getUri()->getScheme() === $this->scheme;
    }
}
