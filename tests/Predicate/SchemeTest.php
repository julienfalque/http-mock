<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Scheme;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Scheme} tests.
 */
class SchemeTest extends TestCase
{
    /**
     * {@see Scheme::__invoke} test.
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke(string $scheme, RequestInterface $request, bool $expected = true)
    {
        $predicate = new Scheme($scheme);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases()
    {
        yield ['http', $request = new Request('GET', 'http://foo')];
        yield ['HTTP', $request];
        yield ['https', $request = new Request('GET', 'https://foo')];
        yield ['HTTPS', $request];
        yield ['foo', $request = new Request('GET', 'foo://foo')];
        yield ['FOO', $request];

        yield ['http', $request = new Request('GET', 'https://foo'), false];
        yield ['HTTP', $request, false];
        yield ['https', $request = new Request('GET', 'http://foo'), false];
        yield ['HTTPS', $request, false];
    }
}
