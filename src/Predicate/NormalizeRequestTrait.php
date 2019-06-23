<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Psr\Http\Message\RequestInterface;

/**
 * Provides methods to normalize a request.
 *
 * @see https://tools.ietf.org/html/rfc1808
 * @see https://tools.ietf.org/html/rfc3986
 */
trait NormalizeRequestTrait
{
    private function normalizeRequest(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();
        $updated = false;

        $normalizedPath = preg_replace('~/{2,}~', '/', $uri->getPath());

        $normalizedPath = preg_replace('~/\\.(/|$)~', '/', $normalizedPath);
        do {
            $normalizedPath = preg_replace('~/[^/]+(?<!\\.\\.)/+\\.\\.(/|$)~', '/', $normalizedPath, 1, $count);
        } while ($count);

        $normalizedPath = $this->decodePercentEncoding($normalizedPath);

        if ($uri->getPath() !== $normalizedPath) {
            $uri = $uri->withPath($normalizedPath);
            $updated = true;
        }

        $normalizedQuery = $this->decodePercentEncoding($uri->getQuery());

        if ($uri->getQuery() !== $normalizedQuery) {
            $uri = $uri->withQuery($normalizedQuery);
            $updated = true;
        }

        if ($updated) {
            return $request->withUri($uri);
        }

        return $request;
    }

    private function decodePercentEncoding(string $urlComponent): string
    {
        $urlComponent = str_replace('%20', '+', $urlComponent);

        return rawurldecode($urlComponent);
    }
}
