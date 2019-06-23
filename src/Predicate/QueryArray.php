<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Matches requests against a list of query string parameters.
 *
 * The expected parameters list can be either a subset of the request's query string or the exact list. In both cases,
 * the order of the values matters.
 *
 * @see \Jfalque\HttpMock\Predicate\Query
 */
class QueryArray implements Predicate
{
    use NormalizeRequestTrait;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var bool
     */
    private $isSubset;

    public function __construct(array $parameters, bool $isSubset = true)
    {
        $this->parameters = $parameters;
        $this->isSubset = $isSubset;
    }

    public function __invoke(RequestInterface $request): bool
    {
        parse_str($this->normalizeRequest($request)->getUri()->getQuery(), $query);

        if ($this->isSubset) {
            return $this->arrayIsSubset($this->parameters, $query);
        }

        return $query === $this->parameters;
    }

    private function arrayIsSubset(array $subset, array $array): bool
    {
        foreach ($subset as $key => $value) {
            if (!\array_key_exists($key, $array)) {
                return false;
            }

            if (\is_array($value)) {
                if (!\is_array($array[$key])) {
                    return false;
                }

                if (!$this->arrayIsSubset($value, $array[$key])) {
                    return false;
                }

                continue;
            }

            if ($value !== $array[$key]) {
                return false;
            }
        }

        return true;
    }
}
