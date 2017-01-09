<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Jfalque\HttpMock\Exception\BadMethodCallException;
use Jfalque\HttpMock\Exception\InvalidArgumentException;
use Jfalque\HttpMock\Server;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * {@see Server} tests.
 */
class ServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@see Server::handle} test.
     */
    public function testHandle()
    {
        $server = new Server();
        self::assertNull($server->handle(new Request('GET', 'http://foo')));
    }

    /**
     * {@see Server::return} test.
     *
     * @param ResponseInterface|callable $result
     * @param ResponseInterface          $expectedResponse
     *
     * @dataProvider getResultCases
     */
    public function testRespond($result, ResponseInterface $expectedResponse)
    {
        $server = new Server();

        self::assertSame($server, $server->return($result));

        self::assertSame(
            $expectedResponse,
            $server->handle(new Request('GET', 'http://foo'))
        );
    }

    /**
     * @return \Generator
     */
    public function getResultCases()
    {
        $response = new Response();

        yield [$response, $response];
        yield [function () use ($response) { return $response; }, $response];
    }

    /**
     * {@see Server::return} test.
     */
    public function testRespondWithCallable()
    {
        $expectedResponse = new Response();

        $server = new Server();
        $server->return(function () use ($expectedResponse) {
            return $expectedResponse;
        });

        self::assertSame(
            $expectedResponse,
            $server->handle(new Request('GET', 'http://foo'))
        );
    }

    /**
     * {@see Server::return} test.
     */
    public function testRespondWithInvalidResult()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Method %s::return() expects argument 1 to be an instance of %s or a callable, integer given.',
            Server::class,
            ResponseInterface::class
        ));

        $server->return(1);
    }

    /**
     * {@see Server::when} test.
     *
     * @param callable         $predicate
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider getWhenCases
     */
    public function testWhen(callable $predicate, RequestInterface $request, bool $expected)
    {
        $this->doWhenTest([$predicate], $request, $expected);
    }

    /**
     * {@see Server::andWhen} test.
     *
     * @param callable         $predicate
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider getWhenCases
     */
    public function testAndWhen(callable $predicate, RequestInterface $request, bool $expected)
    {
        $this->doWhenTest([$predicate], $request, $expected);
    }

    /**
     * @return \Generator
     */
    public function getWhenCases()
    {
        $expectedRequest = new Request('GET', 'http://foo');
        $predicate = function (RequestInterface $request) use ($expectedRequest) {
            return $request === $expectedRequest;
        };

        yield [$predicate, $expectedRequest, true];
        yield [$predicate, new Request('GET', 'http://foo'), false];
    }

    /**
     * {@see Server::whenProtocolVersion} test.
     *
     * @param string|float|int $version
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\ProtocolVersionTest::getMatchingCases
     */
    public function testWhenProtocolVersion($version, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$version], $request, $expected);
    }

    /**
     * {@see Server::andWhenProtocolVersion} test.
     *
     * @param string|float|int $version
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\ProtocolVersionTest::getMatchingCases
     */
    public function testAndWhenProtocolVersion($version, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$version], $request, $expected);
    }

    /**
     * {@see Server::whenMethod} test.
     *
     * @param string|string[]  $method
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\MethodTest::getMatchingCases
     */
    public function testWhenMethod($method, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$method], $request, $expected);
    }

    /**
     * {@see Server::andWhenMethod} test.
     *
     * @param string|string[]  $method
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\MethodTest::getMatchingCases
     */
    public function testAndWhenMethod($method, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$method], $request, $expected);
    }

    /**
     * {@see Server::whenUri} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\UriTest::getMatchingCases
     */
    public function testWhenUri(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::andWhenUri} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\UriTest::getMatchingCases
     */
    public function testAndWhenUri(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::whenScheme} test.
     *
     * @param string           $scheme
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\SchemeTest::getMatchingCases
     */
    public function testWhenScheme(string $scheme, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$scheme], $request, $expected);
    }

    /**
     * {@see Server::andWhenScheme} test.
     *
     * @param string           $scheme
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\SchemeTest::getMatchingCases
     */
    public function testAndWhenScheme(string $scheme, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$scheme], $request, $expected);
    }

    /**
     * {@see Server::whenHost} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\HostTest::getMatchingCases
     */
    public function testWhenHost(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::andWhenHost} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\HostTest::getMatchingCases
     */
    public function testAndWhenHost(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::whenPort} test.
     *
     * @param int|int[]        $port
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\PortTest::getMatchingCases
     */
    public function testWhenPort($port, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$port], $request, $expected);
    }

    /**
     * {@see Server::andWhenPort} test.
     *
     * @param int|int[]        $port
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\PortTest::getMatchingCases
     */
    public function testAndWhenPort($port, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$port], $request, $expected);
    }

    /**
     * {@see Server::whenPath} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\PathTest::getMatchingCases
     */
    public function testWhenPath(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::andWhenPath} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\PathTest::getMatchingCases
     */
    public function testAndWhenPath(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::whenQuery} test.
     *
     * @param string|array     $pattern
     * @param bool|null        $exactMatch
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\QueryTest::getMatchingCases
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\QueryArrayTest::getMatchingCases
     */
    public function testWhenQuery($pattern, $exactMatch, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $exactMatch, $request, $expected);
    }

    /**
     * {@see Server::andWhenQuery} test.
     *
     * @param string|array     $pattern
     * @param bool|null        $exactMatch
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\QueryTest::getMatchingCases
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\QueryArrayTest::getMatchingCases
     */
    public function testAndWhenQuery($pattern, $exactMatch, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $exactMatch, $request, $expected);
    }

    /**
     * {@see Server::whenFragment} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\FragmentTest::getMatchingCases
     */
    public function testWhenFragment(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::andWhenFragment} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\FragmentTest::getMatchingCases
     */
    public function testAndWhenFragment(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::whenHeaders} test.
     *
     * @param array            $expectedHeaders
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\HeadersTest::getMatchingCases
     */
    public function testWhenHeaders(array $expectedHeaders, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$expectedHeaders], $request, $expected);
    }

    /**
     * {@see Server::andWhenHeaders} test.
     *
     * @param array            $expectedHeaders
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\HeadersTest::getMatchingCases
     */
    public function testAndWhenHeaders(array $expectedHeaders, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenTest([$expectedHeaders], $request, $expected);
    }

    /**
     * {@see Server::whenBody} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\BodyTest::getMatchingCases
     */
    public function testWhenBody(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * {@see Server::andWhenBody} test.
     *
     * @param string           $pattern
     * @param bool|null        $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider \Jfalque\HttpMock\Tests\Predicate\BodyTest::getMatchingCases
     */
    public function testAndWhenBody(string $pattern, $regexp, RequestInterface $request, bool $expected = true)
    {
        $this->doWhenWithPatternTest($pattern, $regexp, $request, $expected);
    }

    /**
     * @param array            $arguments
     * @param RequestInterface $request
     * @param bool             $expected
     */
    private function doWhenTest(array $arguments, RequestInterface $request, bool $expected)
    {
        $method = substr($this->getName(false), 4);
        $method[0] = strtolower($method[0]);

        $server = new Server();
        $inner = call_user_func_array([$server, $method], $arguments);

        if (0 === strpos($method, 'when')) {
            self::assertInstanceOf(Server::class, $inner);
            self::assertNotSame($server, $inner);
        } else {
            self::assertSame($server, $inner);
        }

        $inner->return($response = new Response());

        self::assertSame(
            $expected ? $response : null,
            $server->handle($request)
        );
    }

    /**
     * @param mixed            $value
     * @param bool             $regexp
     * @param RequestInterface $request
     * @param bool             $expected
     */
    private function doWhenWithPatternTest($value, bool $regexp, RequestInterface $request, bool $expected)
    {
        if (false === $regexp) {
            $this->doWhenTest([$value], $request, $expected);
        }

        $this->doWhenTest([$value, $regexp], $request, $expected);
    }

    /**
     * {@see Server} fluent API test.
     */
    public function testApi()
    {
        $server = new Server();
        $server
            ->whenHost('foo')
            ->andWhenPath('')
                ->whenMethod('GET')
                    ->return($response1 = new Response())
                ->end()
                ->whenMethod('POST')
                    ->return($response2 = new Response())
                ->end()
                ->whenQuery('/foo=1/', true)
                ->andWhenQuery('/bar=1/', true)
                    ->return($response3 = new Response())
                ->end()
                ->return($response4 = new Response())
            ->end()
            ->whenUri('http://bar')
                ->whenMethod('GET')
                    ->return($response5 = new Response())
                ->end()
            ->end()
        ->end();

        self::assertSame($response1, $server->handle(new Request('GET', 'http://foo')));
        self::assertSame($response2, $server->handle(new Request('POST', 'http://foo')));
        self::assertSame($response3, $server->handle(new Request('PUT', 'http://foo?foo=1&bar=1')));
        self::assertSame($response4, $server->handle(new Request('PUT', 'http://foo')));
        self::assertSame($response5, $server->handle(new Request('GET', 'http://bar')));
        self::assertNull($server->handle(new Request('GET', 'http://non-matching-request')));
    }

    /**
     * {@see Server::__call()} test.
     *
     * @param string $method
     *
     * @dataProvider getMagicMethodMissingArgumentCases
     */
    public function testMagicMethodWithMissingArgument(string $method)
    {
        $server = new Server();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            'Missing argument 1 for method %s::%s().',
            Server::class,
            $method
        ));

        $server->$method();
    }

    /**
     * @return \Generator
     */
    public function getMagicMethodMissingArgumentCases()
    {
        yield ['whenMethod'];
        yield ['whenUri'];
        yield ['whenScheme'];
        yield ['whenHost'];
        yield ['whenPort'];
        yield ['whenPath'];
        yield ['whenQuery'];
        yield ['whenHeaders'];
        yield ['whenBody'];
        yield ['andWhenMethod'];
        yield ['andWhenUri'];
        yield ['andWhenScheme'];
        yield ['andWhenHost'];
        yield ['andWhenPort'];
        yield ['andWhenPath'];
        yield ['andWhenQuery'];
        yield ['andWhenHeaders'];
        yield ['andWhenBody'];
    }

    /**
     * {@see Server::__call()} test.
     *
     * @param string $method
     *
     * @dataProvider getUndefinedMethodCases
     */
    public function testUndefinedMethod(string $method)
    {
        $server = new Server();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            'Method %s::%s() does not exist.',
            Server::class,
            $method
        ));

        $server->$method();
    }

    /**
     * @return \Generator
     */
    public function getUndefinedMethodCases()
    {
        yield ['whenFoo'];
        yield ['andWhenFoo'];
        yield ['foo'];
    }
}
