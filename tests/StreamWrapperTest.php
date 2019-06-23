<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Tests;

use GuzzleHttp\Psr7\Response;
use Jfalque\HttpMock\Server;
use Jfalque\HttpMock\StreamWrapper;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * {@see StreamWrapper} tests.
 */
class StreamWrapperTest extends TestCase
{
    /**
     * @var Server
     */
    private static $server;

    /**
     * @var string|null
     */
    private $iniUserAgent;

    public static function setUpBeforeClass()
    {
        eval(<<<'PHP'
namespace Jfalque\HttpMock;

function stream_wrapper_restore($protocol)
{
    if (in_array($protocol, ['http', 'https'])) {
        stream_wrapper_register($protocol, 'stdClass', STREAM_IS_URL);
        
        return true;
    }

    return \stream_wrapper_restore($protocol);
}

PHP
        );

        self::$server = self::createServer();
    }

    protected function setUp()
    {
        if (!\in_array($this->getName(), ['testRegister', 'testUsageWithoutServer'], true)) {
            StreamWrapper::register(self::$server);
        }
    }

    protected function tearDown()
    {
        if ('testUnregister' !== $this->getName()) {
            StreamWrapper::unregister();
        }

        if (null !== $this->iniUserAgent) {
            ini_set('user_agent', $this->iniUserAgent);
            $this->iniUserAgent = null;
        }
    }

    public static function stearDownAfterClass()
    {
        self::$server = null;
    }

    /**
     * {@see StreamWrapper} test.
     */
    public function testUsageWithoutServer()
    {
        stream_wrapper_unregister('http');
        stream_wrapper_register('http', StreamWrapper::class);

        self::assertFalse(@file_get_contents('http://foo'));

        stream_wrapper_unregister('http');
        stream_wrapper_register('http', StreamWrapper::class, STREAM_IS_URL);

        self::assertFalse(@file_get_contents('http://foo'));
    }

    /**
     * {@see StreamWrapper::register()} test.
     */
    public function testRegister()
    {
        self::assertFalse(@file_get_contents('http://foo'));
        self::assertFalse(@file_get_contents('https://foo'));

        StreamWrapper::register(self::$server);

        $expectedResponse = "[GET] Foo 1\nFoo 2\nFoo 3";

        self::assertSame($expectedResponse, file_get_contents('http://foo'));
        self::assertSame($expectedResponse, file_get_contents('https://foo'));

        stream_wrapper_unregister('http');
        stream_wrapper_unregister('https');
        StreamWrapper::register(self::$server);

        self::assertSame($expectedResponse, file_get_contents('http://foo'));
        self::assertSame($expectedResponse, file_get_contents('https://foo'));
    }

    /**
     * {@see StreamWrapper::unregister()} test.
     */
    public function testUnregister()
    {
        $expectedResponse = "[GET] Foo 1\nFoo 2\nFoo 3";

        self::assertSame($expectedResponse, file_get_contents('http://foo'));
        self::assertSame($expectedResponse, file_get_contents('https://foo'));

        StreamWrapper::unregister();

        self::assertFalse(@file_get_contents('http://foo'));
        self::assertFalse(@file_get_contents('https://foo'));

        StreamWrapper::unregister();

        self::assertFalse(@file_get_contents('http://foo'));
        self::assertFalse(@file_get_contents('https://foo'));
    }

    /**
     * {@see StreamWrapper} test with {@see file_get_contents()}.
     *
     * @param string|false $expectedResult
     * @param string       $defaultUserAgent
     *
     * @dataProvider getRequestCases
     */
    public function testWithFileGetContents(
        $expectedResult,
        string $uri,
        array $options = null,
        string $defaultUserAgent = null
    ) {
        $context = null !== $options ? stream_context_create($options) : null;

        $this->setDefaultUserAgent($defaultUserAgent);

        if (false !== $expectedResult) {
            self::assertSame($expectedResult, file_get_contents($uri, false, $context));

            if (\strlen($expectedResult) >= 8) {
                self::assertSame(
                    substr($expectedResult, 7),
                    file_get_contents($uri, false, $context, 7)
                );
                self::assertSame(
                    $expectedResult[7],
                    file_get_contents($uri, false, $context, 7, 1)
                );
            }
        } else {
            self::assertFalse(@file_get_contents($uri, false, $context));
        }
    }

    /**
     * {@see StreamWrapper} test with {@see fopen()} and related functions.
     *
     * @param string|false $expectedResult
     * @param string       $defaultUserAgent
     *
     * @dataProvider getRequestCases
     */
    public function testWithResourceFunctions(
        $expectedResult,
        string $uri,
        array $options = null,
        string $defaultUserAgent = null
    ) {
        $arguments = [$uri, 'r'];
        if (null !== $options) {
            $arguments[] = false;
            $arguments[] = stream_context_create($options);
        }

        $this->setDefaultUserAgent($defaultUserAgent);

        if (false !== $expectedResult) {
            $resource = \call_user_func_array('fopen', $arguments);
            self::assertTrue(\is_resource($resource));

            $lines = preg_split('/(?<=\\n)/', $expectedResult);

            self::assertFalse(fstat($resource));
            self::assertSame(0, ftell($resource));
            self::assertFalse(feof($resource));
            self::assertSame(substr($lines[0], 0, 7), fgets($resource, 8));
            self::assertSame(min(7, \strlen($lines[0])), ftell($resource));
            self::assertSame(substr($lines[0], 7), fgets($resource));
            self::assertSame(\strlen($lines[0]), ftell($resource));

            if (1 !== \count($lines)) {
                self::assertFalse(feof($resource));
                self::assertSame($lines[1][0], fgetc($resource));
                self::assertSame(substr($lines[1], 1), fgets($resource));
                self::assertSame(substr($lines[2], 0, 2), fread($resource, 2));
                self::assertSame(substr($lines[2], 2), fgets($resource));
                self::assertFalse(fgets($resource));
            }

            self::assertTrue(feof($resource));
            self::assertSame(\strlen($expectedResult), ftell($resource));
            self::assertTrue(fclose($resource));
        } else {
            self::assertFalse(@\call_user_func_array('fopen', $arguments));
        }
    }

    /**
     * {@see StreamWrapper} test with {@see fopen()}.
     *
     * @dataProvider getResourceInWritingModeCases
     */
    public function testWithResourceInWritingMode(string $mode)
    {
        self::assertFalse(@fopen('http://foo', $mode));
    }

    public function getResourceInWritingModeCases()
    {
        yield ['w'];
        yield ['w+'];
        yield ['a'];
        yield ['a+'];
        yield ['x'];
        yield ['x+'];
        yield ['c'];
        yield ['c+'];
    }

    /**
     * {@see StreamWrapper} test with {@see copy()}.
     *
     * @param string|false $expectedResult
     * @param string       $defaultUserAgent
     *
     * @dataProvider getRequestCases
     */
    public function testWithCopy(
        $expectedResult,
        string $uri,
        array $options = null,
        string $defaultUserAgent = null
    ) {
        $root = vfsStream::setup();

        $arguments = [$uri, $destination = $root->url().'/file'];
        if (null !== $options) {
            $arguments[] = stream_context_create($options);
        }

        $this->setDefaultUserAgent($defaultUserAgent);

        if (false !== $expectedResult) {
            self::assertTrue(\call_user_func_array('copy', $arguments));
            self::assertTrue(file_exists($destination));
            self::assertSame($expectedResult, file_get_contents($destination));
        } else {
            self::assertFalse(@\call_user_func_array('copy', $arguments));
            self::assertFalse(file_exists($destination));
        }
    }

    /**
     * {@see StreamWrapper} test with {@see file()}.
     *
     * @param string|false $expectedResult
     * @param string       $defaultUserAgent
     *
     * @dataProvider getRequestCases
     */
    public function testWithFile($expectedResult, string $uri, array $options = null, string $defaultUserAgent = null)
    {
        $arguments = [$uri];
        if (null !== $options) {
            $arguments[] = 0;
            $arguments[] = stream_context_create($options);
        }

        $this->setDefaultUserAgent($defaultUserAgent);

        if (false !== $expectedResult) {
            self::assertSame(
                preg_split('/(?<=\\n)/', $expectedResult),
                \call_user_func_array('file', $arguments)
            );
        } else {
            self::assertFalse(@\call_user_func_array('file', $arguments));
        }
    }

    /**
     * {@see StreamWrapper} test with {@see readfile()}.
     *
     * @param string|false $expectedResult
     * @param string       $defaultUserAgent
     *
     * @dataProvider getRequestCases
     */
    public function testWithReadfile(
        $expectedResult,
        string $uri,
        array $options = null,
        string $defaultUserAgent = null
    ) {
        $arguments = [$uri];
        if (null !== $options) {
            $arguments[] = false;
            $arguments[] = stream_context_create($options);
        }

        $this->setDefaultUserAgent($defaultUserAgent);

        if (false !== $expectedResult) {
            ob_start();
            self::assertSame(\strlen($expectedResult), \call_user_func_array('readfile', $arguments));
            self::assertSame($expectedResult, ob_get_contents());
            ob_end_clean();
        } else {
            self::assertFalse(@\call_user_func_array('readfile', $arguments));
        }
    }

    /**
     * {@see StreamWrapper} test with {@see file_exists()}.
     */
    public function testWithFileExists()
    {
        self::assertFalse(file_exists('http://foo'));
    }

    private static function createServer(): Server
    {
        return (new Server())
            ->whenHost('foo')
                ->whenPath('/')
                    ->whenMethod('GET')
                        ->return(new Response(200, [], "[GET] Foo 1\nFoo 2\nFoo 3"))
                    ->end()
                    ->whenMethod('POST')
                    ->andWhenHeaders([
                        'User-Agent' => 'Foo',
                        'X-Foo' => '1',
                    ])
                    ->andWhenBody('FooBar')
                        ->return(new Response(200, [], "[POST] Foo 1\nFoo 2\nFoo 3"))
                    ->end()
                ->end()
                ->whenPath('/redirections/foo')
                ->andWhenHeaders(['X-Foo' => '1'])
                    ->return(new Response(301, ['Location' => 'foo/bar']))
                ->end()
                ->whenPath('/redirections/foo/bar')
                ->andWhenHeaders(['X-Foo' => '1'])
                    ->return(new Response(302, ['Location' => './baz']))
                ->end()
                ->whenPath('/redirections/foo/baz')
                ->andWhenHeaders(['X-Foo' => '1'])
                    ->return(new Response(301, ['Location' => '../bar']))
                ->end()
                ->whenPath('/redirections/bar')
                ->andWhenHeaders(['X-Foo' => '1'])
                    ->return(new Response(302, ['Location' => '/redirections/bar/foo']))
                ->end()
                ->whenPath('/redirections/bar/foo')
                ->andWhenHeaders(['X-Foo' => '1'])
                    ->return(new Response(200, ['Location' => 'http://foo/redirections/bar/baz']))
                ->end()
                ->whenPath('/redirections/bar/baz')
                ->andWhenHeaders(['X-Foo' => '1'])
                    ->return(new Response(200, [], 'Redirected'))
                ->end()
                ->whenPath('/infinite-redirections')
                    ->return(function (RequestInterface $request) {
                        preg_match('/max=(\\d+)(?:&current=(\\d+))?/', $request->getUri()->getQuery(), $matches);

                        $max = $matches[1];
                        $current = $matches[2] ?? 0;

                        if ($current >= $max) {
                            return new Response(200, [], $current);
                        }

                        return new Response(302, ['Location' => sprintf(
                            'http://foo/infinite-redirections?max=%d&current=%d',
                            $max,
                            $current + 1
                        )]);
                    })
                ->end()
                ->whenPath('/protocol-version')
                    ->return(function (RequestInterface $request) {
                        return new Response(200, [], $request->getProtocolVersion());
                    })
                ->end()
                ->whenPath($regexp = '~^/http-status/(\\d{3})$~', true)
                    ->return(function (RequestInterface $request) use ($regexp) {
                        $status = (int) preg_replace($regexp, '$1', $request->getUri()->getPath());

                        return new Response($status);
                    })
                ->end()
                ->whenPath($regexp = '~^/redirect-to-http-status/(\\d{3})$~', true)
                    ->return(function (RequestInterface $request) use ($regexp) {
                        $status = (int) preg_replace($regexp, '$1', $request->getUri()->getPath());

                        return new Response(301, [
                            'Location' => '/http-status/'.$status,
                        ]);
                    })
                ->end()
            ->end()
        ;
    }

    public function getRequestCases()
    {
        yield ["[GET] Foo 1\nFoo 2\nFoo 3", 'http://foo'];

        yield ["[POST] Foo 1\nFoo 2\nFoo 3", 'http://foo', $options = ['http' => [
            'method' => 'POST',
            'header' => 'X-Foo: 1',
            'user_agent' => 'Foo',
            'content' => 'FooBar',
        ]]];

        yield ["[POST] Foo 1\nFoo 2\nFoo 3", 'http://foo', ['http' => [
            'method' => 'POST',
            'header' => [
                'X-Foo: 1',
                'User-Agent: Foo',
            ],
            'user_agent' => 'Bar',
            'content' => 'FooBar',
        ]]];

        yield ["[POST] Foo 1\nFoo 2\nFoo 3", 'http://foo', ['http' => [
            'method' => 'POST',
            'header' => 'X-Foo: 1',
            'content' => 'FooBar',
        ]], 'Foo'];

        yield ['Redirected', 'http://foo/redirections/foo', ['http' => [
            'method' => 'POST',
            'header' => 'X-Foo: 1',
        ]]];

        yield ['Redirected', 'http://foo/redirections/foo', ['http' => [
            'method' => 'POST',
            'header' => 'X-Foo: 1',
            'follow_location' => 1,
            'max_redirects' => 5,
        ]]];

        yield [false, 'http://foo/redirections/foo', ['http' => [
            'method' => 'POST',
            'header' => 'X-Foo: 1',
            'follow_location' => 1,
            'max_redirects' => 4,
        ]]];

        yield ['20', 'http://foo/infinite-redirections?max=20'];

        yield [false, 'http://foo/infinite-redirections?max=21'];

        yield ['1.0', 'http://foo/protocol-version'];

        yield ['1.0', 'http://foo/protocol-version', ['http' => [
            'protocol_version' => 1.0,
        ]]];

        yield ['1.0', 'http://foo/protocol-version', ['http' => [
            'protocol_version' => '1.0',
        ]]];

        yield ['1.1', 'http://foo/protocol-version', ['http' => [
            'protocol_version' => 1.1,
        ]]];

        yield ['1.1', 'http://foo/protocol-version', ['http' => [
            'protocol_version' => '1.1',
        ]]];

        yield [false, 'http://bar'];

        yield [false, 'http://foo/http-status/400'];

        yield [false, 'http://foo/http-status/404'];

        yield [false, 'http://foo/http-status/500'];

        yield [false, 'http://foo/http-status/503'];

        yield [false, 'http://foo/redirect-to-http-status/400'];

        yield [false, 'http://foo/redirect-to-http-status/404'];

        yield [false, 'http://foo/redirect-to-http-status/500'];

        yield [false, 'http://foo/redirect-to-http-status/503'];
    }

    private function setDefaultUserAgent(string $userAgent = null)
    {
        if (null !== $userAgent) {
            $this->iniUserAgent = ini_set('user_agent', $userAgent);
        }
    }
}
