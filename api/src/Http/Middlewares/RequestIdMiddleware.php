<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Shared\RequestContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

final class RequestIdMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly RequestContext $requestContext)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = $request->getHeaderLine('X-Request-Id');
        if ($requestId === '') {
            $requestId = Uuid::uuid4()->toString();
        }

        $this->requestContext->setRequestId($requestId);

        $response = $handler->handle($request->withAttribute('requestId', $requestId));

        // Every response here is per-cart/per-affiliate dynamic data — the
        // cart id lives in a header, invisible to the browser's URL-keyed cache.
        return $response
            ->withHeader('X-Request-Id', $requestId)
            ->withHeader('Cache-Control', 'no-store');
    }
}
