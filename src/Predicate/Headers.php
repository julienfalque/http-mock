<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against a list of header values.
 *
 * The expected headers list matches when it is a subset of the request's headers (or the same list). Headers order does
 * not matter but the order of their values does.
 */
class Headers implements Predicate
{
    /**
     * @var array
     */
    private $headers;

    /**
     * Constructor.
     *
     * The headers array must contain header names as keys and their values as strings or arrays of strings, e.g.:
     *  * ['X-Foo' => 'foo']
     *  * ['X-Foo' => ['foo']]
     *  * ['X-Foo' => ['foo', 'bar']]
     *
     * @param array $headers
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RequestInterface $request): bool
    {
        foreach ($this->headers as $name => $expectedValues) {
            if ($request->getHeader($name) !== (array) $expectedValues) {
                return false;
            }
        }

        return true;
    }
}
