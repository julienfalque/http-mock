<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Port;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Port} tests.
 */
class PortTest extends TestCase
{
    /**
     * {@see Port::__invoke} test.
     *
     * @param int|int[] $ports
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke($ports, RequestInterface $request, bool $expected = true): void
    {
        $predicate = new Port($ports);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases(): iterable
    {
        yield [80, new Request('GET', 'http://foo')];
        yield [80, new Request('GET', 'http://foo:80')];
        yield [443, new Request('GET', 'https://foo')];
        yield [443, new Request('GET', 'https://foo:443')];
        yield [8080, new Request('GET', 'http://foo:8080')];
        yield [8080, new Request('GET', 'https://foo:8080')];
        yield [[1, 80], new Request('GET', 'http://foo')];
        yield [[1, 80], new Request('GET', 'http://foo:80')];
        yield [[1, 443], new Request('GET', 'https://foo')];
        yield [[1, 443], new Request('GET', 'https://foo:443')];
        yield [[1, 8080], new Request('GET', 'http://foo:8080')];
        yield [[1, 8080], new Request('GET', 'https://foo:8080')];

        yield [1, new Request('GET', 'http://foo'), false];
        yield [1, new Request('GET', 'http://foo:80'), false];
        yield [1, new Request('GET', 'https://foo'), false];
        yield [1, new Request('GET', 'https://foo:443'), false];
        yield [1, new Request('GET', 'http://foo:8080'), false];
        yield [1, new Request('GET', 'https://foo:8080'), false];
        yield [[1, 2], new Request('GET', 'http://foo'), false];
        yield [[1, 2], new Request('GET', 'http://foo:80'), false];
        yield [[1, 2], new Request('GET', 'https://foo'), false];
        yield [[1, 2], new Request('GET', 'https://foo:443'), false];
        yield [[1, 2], new Request('GET', 'http://foo:8080'), false];
        yield [[1, 2], new Request('GET', 'https://foo:8080'), false];
    }
}
