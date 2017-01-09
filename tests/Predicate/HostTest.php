<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Host;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Host} tests.
 */
class HostTest extends PatternPredicateTestCase
{
    /**
     * {@see Host::__invoke} test.
     *
     * @param string           $pattern
     * @param bool             $isRegularExpression
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke(
        string $pattern,
        bool $isRegularExpression,
        RequestInterface $request,
        bool $expected = true
    ) {
        $predicate = new Host($pattern, $isRegularExpression);

        self::assertSame($expected, $predicate($request));
    }

    /**
     * @return \Generator
     */
    public function getMatchingCases()
    {
        yield ['foo', false, $request = new Request('GET', 'http://foo')];
        yield ['/foo/', true, $request];
        yield ['/foo/', true, $request = new Request('GET', 'http://foobar')];

        yield ['foo', false, $request, false];
        yield ['foo', false, $request = new Request('GET', 'http://bar'), false];
        yield ['/foo/', true, $request, false];
    }

    /**
     * {@see Host::__invoke} test.
     */
    public function testInvokeWithInvalidPattern()
    {
        $this->doInvalidPatternTest(new Host('foo', true));
    }
}
