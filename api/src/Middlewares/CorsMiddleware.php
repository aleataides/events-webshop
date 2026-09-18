<?php

declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tuupola\Middleware\CorsMiddleware as TuupolaCorsMiddleware;

/**
 * Wraps Tuupola's CorsMiddleware (final, can't extend) — origin(s) from
 * CORS_ALLOWED_ORIGINS, plus our non-standard cart/request id headers.
 */
final class CorsMiddleware implements MiddlewareInterface
{
    private readonly TuupolaCorsMiddleware $inner;

    public function __construct()
    {
        $origins = array_values(array_filter(array_map(
            'trim',
            explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: 'http://localhost:3000'),
        )));

        $this->inner = new TuupolaCorsMiddleware([
            'origin' => $origins,
            'methods' => ['GET', 'POST', 'PATCH', 'DELETE', 'OPTIONS'],
            'headers.allow' => ['X-Cart-Id', 'X-Request-Id', 'Content-Type'],
            'headers.expose' => ['X-Cart-Id', 'X-Request-Id'],
            'credentials' => false,
            'cache' => 0,
        ]);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->inner->process($request, $handler);
    }
}
