<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Headers;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Headers} tests.
 */
class HeadersTest extends TestCase
{
    /**
     * {@see Headers::__invoke} test.
     *
     * @param array            $headers
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke(array $headers, RequestInterface $request, bool $expected = true)
    {
        $predicate = new Headers($headers);

        self::assertSame($expected, $predicate($request));
    }

    /**
     * @return \Generator
     */
    public function getMatchingCases()
    {
        yield [['X-Foo' => 'foo'], $request = new Request('GET', 'http://foo', ['X-Foo' => 'foo'])];
        yield [['x-foo' => 'foo'], $request];
        yield [['X-Foo' => ['foo']], $request];
        yield [['X-Foo' => 'foo'], new Request('GET', 'http://foo', ['x-foo' => 'foo'])];
        yield [['X-Foo' => ['foo', 'bar']], new Request('GET', 'http://foo', ['X-Foo' => ['foo', 'bar']])];

        yield [['X-Foo' => 'foo'], $request = new Request('GET', 'http://foo'), false];
        yield [['X-Foo' => ['foo', 'bar']], $request, false];
        yield [['X-Foo' => ['foo', 'bar']], new Request('GET', 'http://foo', ['X-Foo' => 'foo']), false];
    }
}
