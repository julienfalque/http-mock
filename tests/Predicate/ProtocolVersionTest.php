<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\ProtocolVersion;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * {@see ProtocolVersion} tests.
 */
class ProtocolVersionTest extends TestCase
{
    /**
     * {@see ProtocolVersion::__invoke} test.
     *
     * @param string|float|int $version
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke($version, RequestInterface $request, bool $expected = true): void
    {
        $predicate = new ProtocolVersion($version);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases(): iterable
    {
        yield ['1.0', $request = new Request('GET', 'http://foo', [], null, '1.0')];
        yield ['1', $request];
        yield [1.0, $request];
        yield [1, $request];
        yield ['1.1', $request = new Request('GET', 'http://foo', [], null, '1.1')];
        yield [1.1, $request];
        yield ['1.11', $request = new Request('GET', 'http://foo', [], null, '1.11')];
        yield [1.11, $request];

        yield ['1.1', $request = new Request('GET', 'http://foo', [], null, '1.0'), false];
        yield [1.1, $request, false];
        yield ['1.1', $request = new Request('GET', 'http://foo', [], null, '1.11'), false];
        yield [1.1, $request, false];
        yield ['1.0', $request = new Request('GET', 'http://foo', [], null, '1.1'), false];
        yield ['1', $request, false];
        yield [1.0, $request, false];
        yield [1, $request, false];
        yield ['1.11', $request = new Request('GET', 'http://foo', [], null, '1.1'), false];
        yield [1.11, $request, false];
    }
}
