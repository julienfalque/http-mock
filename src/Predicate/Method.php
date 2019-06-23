<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against a list of HTTP methods.
 */
class Method implements Predicate
{
    /**
     * @var string[]
     */
    private $methods;

    /**
     * Constructor.
     *
     * @param string|string[] $methods
     */
    public function __construct($methods)
    {
        $this->methods = (array) $methods;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RequestInterface $request): bool
    {
        return \in_array($request->getMethod(), $this->methods, true);
    }
}
