<?php

declare(strict_types=1);

namespace Jfalque\HttpMock\Predicate;

use Jfalque\HttpMock\Exception\RequestNormalizationFailedException;
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

        $normalizedPath = $this->pregReplace('~/{2,}~', '/', $uri->getPath());

        $normalizedPath = $this->pregReplace('~/\\.(/|$)~', '/', $normalizedPath);
        do {
            $normalizedPath = $this->pregReplace('~/[^/]+(?<!\\.\\.)/+\\.\\.(/|$)~', '/', $normalizedPath, 1, $count);
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

    private function pregReplace(string $pattern, string $replacement, string $subject, int $limit = -1, int &$count = null): string
    {
        $result = preg_replace($pattern, $replacement, $subject, $limit, $count);

        if (null === $result) {
            throw RequestNormalizationFailedException::create();
        }

        return $result;
    }
}
