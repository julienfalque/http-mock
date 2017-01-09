<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Path;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Path} tests.
 */
class PathTest extends PatternPredicateTestCase
{
    /**
     * {@see Path::__invoke} test.
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
        $predicate = new Path($pattern, $isRegularExpression);

        self::assertSame($expected, $predicate($request));
    }

    /**
     * @return \Generator
     */
    public function getMatchingCases()
    {
        yield ['', false, $request = new Request('GET', 'http://foo')];
        yield ['/', false, $request];
        yield ['', false, $request = new Request('GET', 'http://foo/')];
        yield ['/', false, $request];
        yield ['/foo', false, $request = new Request('GET', 'http://foo/foo')];
        yield ['/foo/', true, $request];
        yield ['', false, new Request('GET', 'http://foo/foo/..')];
        yield ['/bar', false, new Request('GET', 'http://foo/foo/../bar')];
        yield ['/bar/', false, new Request('GET', 'http://foo/./foo/.././bar/.')];
        yield ['/foo+bar', false, new Request('GET', 'http://foo/foo+bar')];
        yield ['/foo+bar', false, new Request('GET', 'http://foo/foo%20bar')];
        yield ['/foo\+b/', true, new Request('GET', 'http://foo/foo+bar')];
        yield ['/foo\+b/', true, new Request('GET', 'http://foo/foo%20bar')];
        yield ['/@foo', false, new Request('GET', 'http://foo/@foo')];
        yield ['/@foo', false, new Request('GET', 'http://foo/%40foo')];
        yield ['/@fo/', true, new Request('GET', 'http://foo/@foo')];
        yield ['/@fo/', true, new Request('GET', 'http://foo/%40foo')];

        yield ['foo', false, $request = new Request('GET', 'http://foo/bar'), false];
        yield ['/foo', false, $request, false];
        yield ['foo', false, $request, false];
        yield ['/foo/', true, $request, false];
    }

    /**
     * {@see Path::__invoke} test.
     */
    public function testInvokeWithInvalidPattern()
    {
        $this->doInvalidPatternTest(new Path('foo', true));
    }
}
