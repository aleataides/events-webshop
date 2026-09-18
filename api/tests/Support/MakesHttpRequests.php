<?php

declare(strict_types=1);

namespace Tests\Support;

use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Laravel-style HTTP verb helpers for Integration tests.
 */
trait MakesHttpRequests
{
    /**
     * @param array<string, string> $headers
     */
    protected function get(string $uri, array $headers = []): JsonResponse
    {
        return $this->request('GET', $uri, headers: $headers);
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    protected function post(string $uri, array $body = [], array $headers = []): JsonResponse
    {
        return $this->request('POST', $uri, $body, $headers);
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    protected function patch(string $uri, array $body = [], array $headers = []): JsonResponse
    {
        return $this->request('PATCH', $uri, $body, $headers);
    }

    /**
     * @param array<string, string> $headers
     */
    protected function delete(string $uri, array $headers = []): JsonResponse
    {
        return $this->request('DELETE', $uri, headers: $headers);
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    private function request(string $method, string $uri, array $body = [], array $headers = []): JsonResponse
    {
        $request = new ServerRequestFactory()->createServerRequest($method, $uri);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        if ($body !== []) {
            $request = $request->withParsedBody($body);
        }

        return new JsonResponse($this->app()->handle($request));
    }
}
