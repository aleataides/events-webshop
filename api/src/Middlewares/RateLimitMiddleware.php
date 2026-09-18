<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Shared\HttpStatus;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Fixed-window counter per IP, global on all /api routes. Simpler than a
 * true sliding window; fine at this scale, see docs/api/stack.md.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    private const int WINDOW_SECONDS = 60;

    public function __construct(private readonly ClientInterface $redis)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $max = (int) (getenv('RATE_LIMIT_MAX') ?: 60);
        $window = intdiv(time(), self::WINDOW_SECONDS);
        $key = sprintf('ratelimit:%s:%d', $this->resolveIp($request), $window);

        $count = (int) $this->redis->incr($key);
        if ($count === 1) {
            $this->redis->expire($key, self::WINDOW_SECONDS);
        }

        if ($count > $max) {
            $response = new Response(HttpStatus::TooManyRequests->value);
            $response->getBody()->write(json_encode([
                'error' => ['code' => 'rate_limited', 'message' => 'Too many requests.'],
            ], JSON_THROW_ON_ERROR));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Retry-After', (string) self::WINDOW_SECONDS);
        }

        return $handler->handle($request);
    }

    private function resolveIp(ServerRequestInterface $request): string
    {
        $forwardedFor = $request->getHeaderLine('X-Forwarded-For');
        if ($forwardedFor !== '') {
            return trim(explode(',', $forwardedFor)[0]);
        }

        $server = $request->getServerParams();

        return is_string($server['REMOTE_ADDR'] ?? null) ? $server['REMOTE_ADDR'] : 'unknown';
    }
}
