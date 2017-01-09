<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Interface that server matching predicate classes must implement.
 */
interface Predicate
{
    /**
     * Returns whether the given request passes the predicate.
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function __invoke(RequestInterface $request): bool;
}
