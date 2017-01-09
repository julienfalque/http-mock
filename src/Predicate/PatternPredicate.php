<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Jfalque\HttpMock\Exception\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;

/**
 * Base class for pattern-based predicates.
 */
abstract class PatternPredicate implements Predicate
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var bool
     */
    private $isRegularExpression;

    /**
     * Constructor.
     *
     * @param string $pattern
     * @param bool   $isRegularExpression
     */
    public function __construct(string $pattern, bool $isRegularExpression = false)
    {
        $this->pattern = $pattern;
        $this->isRegularExpression = $isRegularExpression;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException when using an invalid
     *                                   {@link http://php.net/manual/en/pcre.pattern.php PCRE pattern}
     */
    public function __invoke(RequestInterface $request): bool
    {
        $value = $this->getValue($request);

        if ($this->isRegularExpression) {
            if (false === $match = @preg_match($this->pattern, $value)) {
                throw new InvalidArgumentException(sprintf(
                    'The regular expression pattern "%s" is invalid: %s.',
                    $this->pattern,
                    substr(error_get_last()['message'], 14)
                ));
            }

            return (bool) $match;
        }

        return $this->pattern === $value;
    }

    /**
     * Returns the value to match against the predicate.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    abstract protected function getValue(RequestInterface $request): string;
}
