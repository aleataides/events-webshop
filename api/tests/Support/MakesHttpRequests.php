<?php

declare(strict_types=1);

namespace Tests\Support;

use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Laravel-style HTTP verb helpers for Integration tests — only GET so far,
 * add post/patch/delete here once Phase 4's mutating endpoints need them.
 */
trait MakesHttpRequests
{
    /**
     * @param array<string, string> $headers
     */
    protected function get(string $uri, array $headers = []): JsonResponse
    {
        $request = new ServerRequestFactory()->createServerRequest('GET', $uri);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return new JsonResponse($this->app()->handle($request));
    }
}
