<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Method;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Method} tests.
 */
class MethodTest extends TestCase
{
    /**
     * {@see Method::__invoke} test.
     *
     * @param string|string[] $methods
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke($methods, RequestInterface $request, bool $expected = true): void
    {
        $predicate = new Method($methods);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases(): iterable
    {
        yield ['FOO', $request = new Request('FOO', 'http://foo')];
        yield [['FOO'], $request];
        yield [['FOO', 'BAR'], $request];

        yield ['FOO', $request = new Request('BAZ', 'http://foo'), false];
        yield [['FOO'], $request, false];
        yield [['FOO', 'BAR'], $request, false];
    }
}
