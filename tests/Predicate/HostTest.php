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
     * @dataProvider getMatchingCases
     */
    public function testInvoke(
        string $pattern,
        bool $isRegularExpression,
        RequestInterface $request,
        bool $expected = true
    ): void {
        $predicate = new Host($pattern, $isRegularExpression);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases(): iterable
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
    public function testInvokeWithInvalidPattern(): void
    {
        $this->doInvalidPatternTest(new Host('foo', true));
    }
}
