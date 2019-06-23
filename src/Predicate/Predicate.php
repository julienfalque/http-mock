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
     */
    public function __invoke(RequestInterface $request): bool;
}
