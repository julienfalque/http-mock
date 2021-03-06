<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Body;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Body} tests.
 */
class BodyTest extends PatternPredicateTestCase
{
    /**
     * {@see Body::__invoke} test.
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke(
        string $pattern,
        bool $isRegularExpression,
        RequestInterface $request,
        bool $expected = true
    ): void {
        $predicate = new Body($pattern, $isRegularExpression);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases(): iterable
    {
        yield ['Foo', false, $request = new Request('GET', 'http://foo', [], 'Foo')];
        yield ['/Foo/', true, $request];
        yield ['/Foo/', true, new Request('GET', 'http://foo', [], 'FooBar')];

        yield ['Foo', false, new Request('GET', 'http://foo', [], 'FooBar'), false];
        yield ['Foo', false, $request = new Request('GET', 'http://foo', [], 'Bar'), false];
        yield ['/Foo/', true, $request, false];
    }

    /**
     * {@see Body::__invoke} test.
     */
    public function testInvokeWithInvalidPattern(): void
    {
        $this->doInvalidPatternTest(new Body('foo', true));
    }
}
