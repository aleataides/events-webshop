<?php

declare(strict_types=1);

namespace App\Http\Routing;

use App\Entities\Affiliate;
use App\Exceptions\MissingAffiliateContextException;
use App\Http\Requests\FormRequest;
use Closure;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Calls controllers as `(...$params): Response`, resolving each `__invoke`
 * parameter by its type-hint — see docs/conventions.md.
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

        $parameters = new ReflectionFunction(Closure::fromCallable($callable))->getParameters();
        $arguments = array_map(fn (ReflectionParameter $parameter) => $this->resolveArgument($parameter, $request), $parameters);

        return $callable(...$arguments);
    }

    private function resolveArgument(ReflectionParameter $parameter, ServerRequestInterface $request): mixed
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException("Controller parameter \"{$parameter->getName()}\" must have a class type-hint.");
        }

        $class = $type->getName();

        return match (true) {
            is_a($request, $class) => $request,
            is_subclass_of($class, FormRequest::class) => $class::fromHttpRequest($request),
            $class === Affiliate::class => $this->affiliate($request),
            default => throw new LogicException("Cannot resolve controller parameter of type \"{$class}\"."),
        };
    }

    private function affiliate(ServerRequestInterface $request): Affiliate
    {
        $affiliate = $request->getAttribute('affiliate');
        if (!$affiliate instanceof Affiliate) {
            throw new MissingAffiliateContextException();
        }

        return $affiliate;
    }
}
