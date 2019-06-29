<?php

declare(strict_types=1);

namespace Jfalque\HttpMock;

use GuzzleHttp\Psr7\Request;
use Jfalque\HttpMock\Exception\NoHttpResponseAvailableException;
use Psr\Http\Message\ResponseInterface;

/**
 * Integrates {@see Server} into native filesystem functions for HTTP and HTTPS.
 *
 * @see http://php.net/manual/en/class.streamwrapper.php
 */
final class StreamWrapper
{
    /**
     * @var ServerInterface|null
     */
    private static $server;

    /**
     * @see http://php.net/manual/en/class.streamwrapper.php#streamwrapper.props.context
     *
     * @var resource|null
     */
    public $context;

    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * Registers the stream wrapper.
     *
     * The given {@see ServerInterface} will be used to handle all HTTP requests. All subsequent filesystem function
     * calls over HTTP will use this stream wrapper. Also, if a custom wrapper was already registered, it will be
     * replaced.
     *
     * The wrapper can be unregistered using {@see unregister()} method. Note that this will restore the PHP built-in
     * wrapper; if you want to use another custom wrapper, you will have to register it again.
     */
    public static function register(ServerInterface $server): void
    {
        self::$server = $server;

        $wrappers = stream_get_wrappers();

        foreach (['http', 'https'] as $protocol) {
            if (\in_array($protocol, $wrappers, true)) {
                stream_wrapper_unregister($protocol);
            }

            stream_wrapper_register($protocol, self::class, STREAM_IS_URL);
        }
    }

    /**
     * Restores PHP built-in HTTP wrapper.
     */
    public static function unregister(): void
    {
        @stream_wrapper_restore('http');
        @stream_wrapper_restore('https');

        self::$server = null;
    }

    /**
     * @see http://php.net/manual/en/streamwrapper.stream-open.php
     *
     * @param string $openedPath
     */
    public function stream_open(string $uri, string $mode, int $options, &$openedPath): bool
    {
        if (null === self::$server) {
            trigger_error(sprintf(
                'HTTP wrapper must be registered using %s::register() method',
                self::class
            ), E_USER_WARNING);

            return false;
        }

        if (\in_array($mode, ['w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'], true)) {
            trigger_error('HTTP wrapper does not support writeable connections', E_USER_WARNING);

            return false;
        }

        $protocolVersion = 1.0;
        $method = 'GET';
        $headers = [];
        $body = null;
        $userAgent = ini_get('user_agent');

        $followRedirects = true;
        $maxRedirects = 20;

        if (null !== $this->context) {
            $contextOptions = stream_context_get_options($this->context);
            $contextOptions = $contextOptions['http'] ?? [];

            if (isset($contextOptions['method'])) {
                $method = $contextOptions['method'];
            }

            if (isset($contextOptions['header'])) {
                foreach ((array) $contextOptions['header'] as $header) {
                    if (!\is_string($header) || false === strpos($header, ':')) {
                        continue;
                    }

                    [$name, $value] = preg_split('/:\\s*/', $header, 2);
                    if (!isset($headers[$name])) {
                        $headers[$name] = $value;
                    }
                }
            }

            if (isset($contextOptions['user_agent'])) {
                $userAgent = $contextOptions['user_agent'];
            }

            if (isset($contextOptions['content'])) {
                $body = $contextOptions['content'];
            }

            if (isset($contextOptions['follow_location'])) {
                $followRedirects = (bool) $contextOptions['follow_location'];
            }

            if (isset($contextOptions['max_redirects'])) {
                $maxRedirects = (int) $contextOptions['max_redirects'];
            }

            if (isset($contextOptions['protocol_version'])) {
                $protocolVersion = (float) $contextOptions['protocol_version'];
            }
        }

        $protocolVersion = sprintf('%01.1f', $protocolVersion);

        if ('' !== $userAgent && !\in_array('user-agent', array_map('strtolower', array_keys($headers)), true)) {
            $headers['User-Agent'] = $userAgent;
        }

        $request = new Request($method, $uri, $headers, $body, $protocolVersion);

        if (null === $this->response = self::$server->handle($request)) {
            return $this->triggerError(sprintf(
                'The server used by HTTP wrapper returned no response for request %s %s.',
                $request->getMethod(),
                $request->getUri()
            ), $options);
        }

        if ($this->response->getStatusCode() >= 400) {
            return $this->triggerError(sprintf(
                'Request %s %s failed: %d %d.',
                $request->getMethod(),
                $request->getUri(),
                $this->response->getStatusCode(),
                $this->response->getReasonPhrase()
            ), $options);
        }

        if ($followRedirects) {
            $redirections = 0;
            $currentRequest = $request;
            while (([] !== $redirectUri = $this->response->getHeader('Location')) && '' !== $redirectUri = $redirectUri[0]) {
                $requestUri = $currentRequest->getUri();

                if (false === strpos($redirectUri, '://')) {
                    if (0 !== strpos($redirectUri, '/')) {
                        $path = $requestUri->getPath();
                        /** @var int $lastPosition */
                        $lastPosition = strrpos($path, '/');
                        $redirectUri = substr($path, 0, $lastPosition + 1).'/'.$redirectUri;
                    }

                    if (null !== $port = $requestUri->getPort()) {
                        $redirectUri = ':'.$port.$redirectUri;
                    }

                    $redirectUri = $requestUri->getScheme().'://'.$requestUri->getHost().$redirectUri;
                }

                if ($redirections++ >= $maxRedirects) {
                    return $this->triggerError(sprintf(
                        'Request %s %s got redirected to %s but redirection limit (%d) has been reached.',
                        $request->getMethod(),
                        $request->getUri(),
                        $redirectUri,
                        $maxRedirects
                    ), $options);
                }

                $currentRequest = new Request('GET', $redirectUri, $headers, null, $protocolVersion);

                if (null === $this->response = self::$server->handle($currentRequest)) {
                    return $this->triggerError(sprintf(
                        'Request %s %s got redirected to %s but no response was returned by the server.',
                        $request->getMethod(),
                        $request->getUri(),
                        $redirectUri
                    ), $options);
                }

                if ($this->response->getStatusCode() >= 400) {
                    return $this->triggerError(sprintf(
                        'Request %s %s failed: %d %d.',
                        $request->getMethod(),
                        $request->getUri(),
                        $this->response->getStatusCode(),
                        $this->response->getReasonPhrase()
                    ), $options);
                }
            }
        }

        $body = $this->response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        return true;
    }

    /**
     * @see http://php.net/manual/en/streamwrapper.stream-stat.php
     *
     * @return array|false
     */
    public function stream_stat()
    {
        return false;
    }

    /**
     * @see http://php.net/manual/en/streamwrapper.stream-read.php
     */
    public function stream_read(int $length): string
    {
        if (null === $this->response) {
            throw NoHttpResponseAvailableException::create();
        }

        return $this->response->getBody()->read($length);
    }

    /**
     * @see http://php.net/manual/en/streamwrapper.stream-seek.php
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        if (null === $this->response) {
            throw NoHttpResponseAvailableException::create();
        }

        $body = $this->response->getBody();
        if (!$body->isSeekable()) {
            return false;
        }

        try {
            $body->seek($offset, $whence);

            return true;
        } catch (\RuntimeException $exception) {
            return false;
        }
    }

    /**
     * @see http://php.net/manual/en/streamwrapper.stream-tell.php
     */
    public function stream_tell(): int
    {
        if (null === $this->response) {
            throw NoHttpResponseAvailableException::create();
        }

        return $this->response->getBody()->tell();
    }

    /**
     * @see http://php.net/manual/en/streamwrapper.stream-eof.php
     */
    public function stream_eof(): bool
    {
        if (null === $this->response) {
            throw NoHttpResponseAvailableException::create();
        }

        return $this->response->getBody()->eof();
    }

    /**
     * @see http://php.net/manual/en/streamwrapper.url-stat.php
     *
     * @return array|false
     */
    public function url_stat()
    {
        return false;
    }

    private function triggerError(string $message, int $options): bool
    {
        if (1 === ($options & STREAM_REPORT_ERRORS)) {
            trigger_error($message, E_USER_WARNING);
        }

        return false;
    }
}
