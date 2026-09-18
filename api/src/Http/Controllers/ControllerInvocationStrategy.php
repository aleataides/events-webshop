<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Calls controllers as `(Request $request): Response` — no $response param
 * to thread through (Controller::json() builds its own), route args land
 * as request attributes, same as Slim's default RequestResponse strategy.
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

        return $callable($request);
    }
}
