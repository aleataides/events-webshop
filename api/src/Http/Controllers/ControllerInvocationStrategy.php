<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FormRequest;
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionNamedType;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Calls controllers as `(Request $request): Response` — no $response param
 * to thread through (Controller::json() builds its own), route args land
 * as request attributes, same as Slim's default RequestResponse strategy.
 * If the controller type-hints a `FormRequest` subclass instead, it's
 * built via `fromHttpRequest()` and passed in place of the raw request.
 */
final class ControllerInvocationStrategy implements InvocationStrategyInterface
{
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments,
    ): ResponseInterface {
        foreach ($routeArguments as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $callable($this->resolveArgument($callable, $request));
    }

    private function resolveArgument(callable $callable, ServerRequestInterface $request): ServerRequestInterface|FormRequest
    {
        $parameter = new ReflectionFunction(Closure::fromCallable($callable))->getParameters()[0] ?? null;
        $type = $parameter?->getType();

        if ($type instanceof ReflectionNamedType && is_subclass_of($type->getName(), FormRequest::class)) {
            return $type->getName()::fromHttpRequest($request);
        }

        return $request;
    }
}
