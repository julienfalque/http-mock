<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against a list of port numbers.
 */
class Port implements Predicate
{
    /**
     * @var int[]
     */
    private $ports;

    /**
     * @param int|int[] $ports
     */
    public function __construct($ports)
    {
        $this->ports = (array) $ports;
    }

    public function __invoke(RequestInterface $request): bool
    {
        if (null === $port = $request->getUri()->getPort()) {
            $port = $this->getDefaultPort($request);
        }

        return \in_array($port, $this->ports, true);
    }

    private function getDefaultPort(RequestInterface $request): ?int
    {
        switch ($request->getUri()->getScheme()) {
            case 'http':
                return 80;
            case 'https':
                return 443;
        }

        return null;
    }
}
