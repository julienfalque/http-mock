<?php

declare(strict_types=1);

namespace Jfalque\HttpMock;

use Jfalque\HttpMock\Exception\BadMethodCallException;
use Jfalque\HttpMock\Exception\InvalidArgumentException;
use Jfalque\HttpMock\Predicate\Body;
use Jfalque\HttpMock\Predicate\Fragment;
use Jfalque\HttpMock\Predicate\Headers;
use Jfalque\HttpMock\Predicate\Host;
use Jfalque\HttpMock\Predicate\Method;
use Jfalque\HttpMock\Predicate\Path;
use Jfalque\HttpMock\Predicate\Port;
use Jfalque\HttpMock\Predicate\ProtocolVersion;
use Jfalque\HttpMock\Predicate\Query;
use Jfalque\HttpMock\Predicate\QueryArray;
use Jfalque\HttpMock\Predicate\Scheme;
use Jfalque\HttpMock\Predicate\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Server class with fluent API to match requests and return responses.
 *
 * @method Server whenProcotolVersion(string|float|int $version)
 * @method Server whenMethod(string|string[] $method)
 * @method Server whenUri(string $uri, bool $regexp = false)
 * @method Server whenScheme(string $scheme)
 * @method Server whenHost(string $host, bool $regexp = false)
 * @method Server whenPort(int|int[] $port)
 * @method Server whenPath(string $path, bool $regexp = false)
 * @method Server whenQuery(string|array $query, bool $regexpOrSubset = false)
 * @method Server whenFragment(string $fragment, bool $regexp = false)
 * @method Server whenHeaders(array $headers)
 * @method Server whenBody(string $body, bool $regexp = false)
 * @method Server andWhenProcotolVersion(string|float|int $version)
 * @method Server andWhenMethod(string|string[] $method)
 * @method Server andWhenUri(string $uri, bool $regexp = false)
 * @method Server andWhenScheme(string $scheme)
 * @method Server andWhenHost(string $host, bool $regexp = false)
 * @method Server andWhenPort(int|int[] $port)
 * @method Server andWhenPath(string $path, bool $regexp = false)
 * @method Server andWhenQuery(string|array $query, bool $regexpOrSubset = false)
 * @method Server andWhenFragment(string $fragment, bool $regexp = false)
 * @method Server andWhenHeaders(array $headers)
 * @method Server andWhenBody(string $body, bool $regexp = false)
 */
final class Server implements ServerInterface
{
    /**
     * @var self|null
     */
    private $parent;

    /**
     * @var self[]
     */
    private $layers = [];

    /**
     * @var callable[]
     */
    private $predicates = [];

    /**
     * @var ResponseInterface|callable
     */
    private $result;

    /**
     * Creates a new matching layer with the given predicate and returns it.
     *
     * The predicate must be a callable that accepts a {@see RequestInterface} instance as first parameter and returns a
     * boolean that represents whether the request matches the predicate's criteria.
     *
     * @return self The new matching layer
     */
    public function when(callable $predicate): self
    {
        $layer = new self();
        $layer->parent = $this;
        $layer->predicates[] = $predicate;

        $this->layers[] = $layer;

        return $layer;
    }

    /**
     * Adds a predicate to the current matching layer.
     *
     * The predicate must be a callable that accepts a {@see RequestInterface} instance as first parameter and returns a
     * boolean that represents whether the request matches the predicate's criteria.
     *
     * @return $this The current matching layer
     */
    public function andWhen(callable $predicate): self
    {
        $this->predicates[] = $predicate;

        return $this;
    }

    /**
     * Handles specific `when*` and `andWhen*` calls.
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $arguments): self
    {
        if (!preg_match('/^(when|andWhen)(.+)$/', $method, $matches)) {
            throw BadMethodCallException::undefinedMethod($this, $method);
        }

        $getValue = function () use ($arguments, $method) {
            if (!\array_key_exists(0, $arguments)) {
                throw BadMethodCallException::missingArgument($this, $method, 1);
            }

            return $arguments[0];
        };

        $regexp = $arguments[1] ?? false;

        switch ($matches[2]) {
            case 'ProtocolVersion':
                $predicate = new ProtocolVersion($getValue());

                break;
            case 'Scheme':
                $predicate = new Scheme($getValue());

                break;
            case 'Uri':
                $predicate = new Uri($getValue(), $regexp);

                break;
            case 'Method':
                $predicate = new Method($getValue());

                break;
            case 'Host':
                $predicate = new Host($getValue(), $regexp);

                break;
            case 'Port':
                $predicate = new Port($getValue());

                break;
            case 'Path':
                $predicate = new Path($getValue(), $regexp);

                break;
            case 'Query':
                if (\is_array($value = $getValue())) {
                    $predicate = new QueryArray($value, $regexp);
                } else {
                    $predicate = new Query($value, $regexp);
                }

                break;
            case 'Fragment':
                $predicate = new Fragment($getValue(), $regexp);

                break;
            case 'Headers':
                $predicate = new Headers($getValue());

                break;
            case 'Body':
                $predicate = new Body($getValue(), $regexp);

                break;
            default:
                throw BadMethodCallException::undefinedMethod($this, $method);
        }

        $genericMethod = $matches[1];

        return $this->{$genericMethod}($predicate);
    }

    /**
     * Defines the result for the current matching layer.
     *
     * If the result is a callable, it must accept a {@see RequestInterface} as first parameter and return an instance
     * of {@see ResponseInterface}.
     *
     * @param ResponseInterface|callable $result
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function return($result)
    {
        if (!$result instanceof ResponseInterface && !\is_callable($result)) {
            throw InvalidArgumentException::withExpectedType(
                $this,
                'return',
                1,
                sprintf('an instance of %s or a callable', ResponseInterface::class),
                $result
            );
        }

        $this->result = $result;

        return $this;
    }

    /**
     * Ends the current matching layer definition and returns its parent, if any.
     *
     * @return self|null
     */
    public function end()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(RequestInterface $request)
    {
        foreach ($this->predicates as $predicate) {
            if (!$predicate($request)) {
                return null;
            }
        }

        foreach ($this->layers as $layer) {
            if (null !== $result = $layer->handle($request)) {
                return $result;
            }
        }

        if (\is_callable($result = $this->result)) {
            return $result($request);
        }

        return $result;
    }
}
