<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Enums\HttpStatus;
use App\Http\JsonResponse;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Fixed-window counter per IP, global on all /api routes. Simpler than a
 * true sliding window; fine at this scale, see docs/api/stack.md.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ClientInterface $redis)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $max = (int) (getenv('RATE_LIMIT_MAX') ?: 60);
        $windowSeconds = (int) (getenv('RATE_LIMIT_WINDOW_SECONDS') ?: 60);
        $window = intdiv(time(), $windowSeconds);
        $key = sprintf('ratelimit:%s:%d', $this->resolveIp($request), $window);

        $count = (int) $this->redis->incr($key);
        if ($count === 1) {
            $this->redis->expire($key, $windowSeconds);
        }

        if ($count > $max) {
            return new JsonResponse([
                'error' => ['code' => 'rate_limited', 'message' => 'Too many requests.'],
            ], HttpStatus::TooManyRequests->value)->withHeader('Retry-After', (string) $windowSeconds);
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
