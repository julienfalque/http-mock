<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests\Predicate;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Predicate\Fragment;
use Psr\Http\Message\RequestInterface;

/**
 * {@see Fragment} tests.
 */
class FragmentTest extends PatternPredicateTestCase
{
    /**
     * {@see Fragment::__invoke} test.
     *
     * @dataProvider getMatchingCases
     */
    public function testInvoke(
        string $pattern,
        bool $isRegularExpression,
        RequestInterface $request,
        bool $expected = true
    ) {
        $predicate = new Fragment($pattern, $isRegularExpression);

        self::assertSame($expected, $predicate($request));
    }

    public function getMatchingCases()
    {
        yield ['foo', false, $request = new Request('GET', 'http://foo#foo')];
        yield ['/foo/', true, $request];
        yield ['/foo/', true, $request = new Request('GET', 'http://foo#foobar')];

        yield ['foo', false, $request, false];
        yield ['foo', false, $request = new Request('GET', 'http://foo#bar'), false];
        yield ['/foo/', true, $request, false];
    }

    /**
     * {@see Fragment::__invoke} test.
     */
    public function testInvokeWithInvalidPattern()
    {
        $this->doInvalidPatternTest(new Fragment('foo', true));
    }
}
