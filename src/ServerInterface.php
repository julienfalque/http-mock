<?php

declare(strict_types=1);

namespace Jfalque\HttpMock;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface that server classes must implement.
 */
interface ServerInterface
{
    /**
     * Handles a {@see RequestInterface} instance and returns the matching response, if any.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface|null
     */
    public function handle(RequestInterface $request);
}
