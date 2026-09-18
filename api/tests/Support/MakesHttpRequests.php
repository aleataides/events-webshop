<?php

declare(strict_types=1);

namespace Tests\Support;

use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Laravel-style HTTP verb helpers for Integration tests — only GET so far,
 * add post/patch/delete here once Phase 4's cart endpoints need them.
 */
trait MakesHttpRequests
{
    protected function get(string $uri): JsonResponse
    {
        $request = new ServerRequestFactory()->createServerRequest('GET', $uri);

        return new JsonResponse($this->app()->handle($request));
    }
}
