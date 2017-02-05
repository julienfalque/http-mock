<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\QueryArray;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * {@see QueryArray} tests.
 */
class QueryArrayTest extends TestCase
{
    /**
     * {@see QueryArray::__invoke} test.
     *
     * @param array            $parameters
     * @param bool             $isSubset
     * @param RequestInterface $request
     * @param bool             $expected
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke(array $parameters, bool $isSubset, RequestInterface $request, bool $expected = true)
    {
        $predicate = new QueryArray($parameters, $isSubset);

        self::assertSame($expected, $predicate($request));
    }

    /**
     * @return \Generator
     */
    public function getMatchingCases()
    {
        yield [['foo' => 'bar'], true, new Request('GET', 'http://foo?foo=bar')];
        yield [['foo' => 'bar'], true, new Request('GET', 'http://foo?foo=bar&bar=baz')];
        yield [['foo' => ['bar']], true, new Request('GET', 'http://foo?foo[]=bar&foo[]=baz')];
        yield [['foo' => 'bar'], false, new Request('GET', 'http://foo?foo=bar')];
        yield [['foo' => 'bar', 'bar' => 'baz'], false, new Request('GET', 'http://foo?foo=bar&bar=baz')];
        yield [['foo' => ['bar', 'baz']], false, new Request('GET', 'http://foo?foo[]=bar&foo[]=baz')];
        yield [['foo' => 'foo bar'], false, new Request('GET', 'http://foo?foo=foo+bar')];
        yield [['foo' => 'foo bar'], false, new Request('GET', 'http://foo?foo=foo%20bar')];
        yield [['foo' => ['foo bar']], false, new Request('GET', 'http://foo?foo[]=foo+bar')];
        yield [['foo' => ['foo bar']], false, new Request('GET', 'http://foo?foo[]=foo%20bar')];
        yield [['foo' => 'foo bar'], true, new Request('GET', 'http://foo?foo=foo+bar&bar=baz')];
        yield [['foo' => 'foo bar'], true, new Request('GET', 'http://foo?foo=foo%20bar&bar=baz')];
        yield [['foo' => ['foo bar']], true, new Request('GET', 'http://foo?foo[]=foo+bar&bar=baz')];
        yield [['foo' => ['foo bar']], true, new Request('GET', 'http://foo?foo[]=foo%20bar&bar=baz')];
        yield [['@foo' => '@foo'], false, new Request('GET', 'http://foo?%40foo=%40foo')];
        yield [['@foo' => ['@foo']], false, new Request('GET', 'http://foo?@foo[]=@foo')];
        yield [['@foo' => ['@foo']], false, new Request('GET', 'http://foo?%40foo[]=%40foo')];
        yield [['@foo' => '@foo'], true, new Request('GET', 'http://foo?@foo=@foo&bar=baz')];
        yield [['@foo' => '@foo'], true, new Request('GET', 'http://foo?%40foo=%40foo&bar=baz')];
        yield [['@foo' => ['@foo']], true, new Request('GET', 'http://foo?@foo[]=@foo&bar=baz')];
        yield [['@foo' => ['@foo']], true, new Request('GET', 'http://foo?%40foo[]=%40foo&bar=baz')];

        yield [['foo' => 'bar'], true, new Request('GET', 'http://foo'), false];
        yield [['foo' => 'bar'], true, new Request('GET', 'http://foo?foo=baz'), false];
        yield [['foo' => 'bar'], true, new Request('GET', 'http://foo?foo[]=bar'), false];
        yield [['foo' => 'bar'], false, new Request('GET', 'http://foo'), false];
        yield [['foo' => 'bar'], false, new Request('GET', 'http://foo?foo=bar&bar=baz'), false];
        yield [['foo' => ['bar']], false, new Request('GET', 'http://foo?foo[]=bar&foo[]=baz'), false];
    }
}
