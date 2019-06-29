<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Query;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Query} tests.
 */
class QueryTest extends PatternPredicateTestCase
{
    /**
     * {@see Query::__invoke} test.
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke(
        string $pattern,
        bool $isRegularExpression,
        RequestInterface $request,
        bool $expected = true
    ): void {
        $predicate = new Query($pattern, $isRegularExpression);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases(): iterable
    {
        yield ['foo=bar', false, $request = new Request('GET', 'http://foo?foo=bar')];
        yield ['/foo=/', true, $request];
        yield ['foo=foo+bar', false, new Request('GET', 'http://foo?foo=foo+bar')];
        yield ['foo=foo+bar', false, new Request('GET', 'http://foo?foo=foo%20bar')];
        yield ['/foo\\+bar/', true, new Request('GET', 'http://foo?foo=foo+bar')];
        yield ['/foo\\+bar/', true, new Request('GET', 'http://foo?foo=foo%20bar')];
        yield ['@foo=@bar', false, new Request('GET', 'http://foo?@foo=@bar')];
        yield ['@foo=@bar', false, new Request('GET', 'http://foo?%40foo=%40bar')];
        yield ['/@fo/', true, new Request('GET', 'http://foo?@foo=@bar')];
        yield ['/@fo/', true, new Request('GET', 'http://foo?%40foo=%40bar')];

        yield ['foo=bar', false, $request = new Request('GET', 'http://foo?bar=foo'), false];
        yield ['/foo=/', true, $request, false];
    }

    /**
     * {@see Query::__invoke} test.
     */
    public function testInvokeWithInvalidPattern(): void
    {
        $this->doInvalidPatternTest(new Query('foo', true));
    }
}
