<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Uri;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Uri} tests.
 */
class UriTest extends PatternPredicateTestCase
{
    /**
     * {@see Uri::__invoke} test.
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke(
        string $pattern,
        bool $isRegularExpression,
        RequestInterface $request,
        bool $expected = true
    ) {
        $predicate = new Uri($pattern, $isRegularExpression);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases()
    {
        yield ['http://foo', false, $request = new Request('GET', 'http://foo')];
        yield ['http://foo/', false, $request];
        yield ['http://foo', false, $request = new Request('GET', 'http://foo/')];
        yield ['http://foo/', false, $request];
        yield ['http://foo?foo', false, $request = new Request('GET', 'http://foo?foo')];
        yield ['http://foo/?foo', false, $request];
        yield ['http://foo?foo', false, $request = new Request('GET', 'http://foo/?foo')];
        yield ['http://foo/?foo', false, $request];
        yield ['http://foo#foo', false, $request = new Request('GET', 'http://foo#foo')];
        yield ['http://foo/#foo', false, $request];
        yield ['http://foo#foo', false, $request = new Request('GET', 'http://foo/#foo')];
        yield ['http://foo/#foo', false, $request];
        yield ['/f/', true, $request];
        yield ['http://foo', false, new Request('GET', 'http://foo/foo/..')];
        yield ['http://foo/bar', false, new Request('GET', 'http://foo/foo/../bar')];
        yield ['http://foo/bar/', false, new Request('GET', 'http://foo/./foo/.././bar/.')];
        yield ['http://foo/foo+bar', false, new Request('GET', 'http://foo/foo+bar')];
        yield ['http://foo/foo+bar', false, new Request('GET', 'http://foo/foo%20bar')];
        yield ['http://foo?foo=foo+bar/', false, new Request('GET', 'http://foo?foo=foo+bar')];
        yield ['http://foo?foo=foo+bar/', false, new Request('GET', 'http://foo?foo=foo%20bar')];
        yield ['/foo\\+b/', true, new Request('GET', 'http://foo/foo+bar')];
        yield ['/foo\\+b/', true, new Request('GET', 'http://foo/foo%20bar')];
        yield ['/foo\\+b/', true, new Request('GET', 'http://foo?foo=foo+bar')];
        yield ['/foo\\+b/', true, new Request('GET', 'http://foo?foo=foo%20bar')];
        yield ['http://foo/@foo', false, new Request('GET', 'http://foo/@foo')];
        yield ['http://foo/@foo', false, new Request('GET', 'http://foo/%40foo')];
        yield ['http://foo?@foo=@bar/', false, new Request('GET', 'http://foo?@foo=@bar')];
        yield ['http://foo?@foo=@bar/', false, new Request('GET', 'http://foo?%40foo=%40bar')];
        yield ['/@fo/', true, new Request('GET', 'http://foo/@foo')];
        yield ['/@fo/', true, new Request('GET', 'http://foo/%40foo')];
        yield ['/@fo/', true, new Request('GET', 'http://foo?@foo=@bar')];
        yield ['/@fo/', true, new Request('GET', 'http://foo?%40foo=%40bar')];

        yield ['foo', false, $request = new Request('GET', 'http://bar'), false];
        yield ['/f/', false, $request, false];
    }

    /**
     * {@see Uri::__invoke} test.
     */
    public function testInvokeWithInvalidPattern()
    {
        $this->doInvalidPatternTest(new Uri('foo', true));
    }
}
