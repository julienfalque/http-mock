<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against a protocol version.
 */
class ProtocolVersion implements Predicate
{
    /**
     * @var string
     */
    private $version;

    /**
     * @param string|float|int $version
     */
    public function __construct($version)
    {
        $this->version = (float) $version;
    }

    public function __invoke(RequestInterface $request): bool
    {
        return (float) $request->getProtocolVersion() === $this->version;
    }
}
