<?php

declare(strict_types=1);

namespace Tests\Unit\Middlewares;

use App\Middlewares\RateLimitMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Support\FakeRedis;

final class RateLimitMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('RATE_LIMIT_MAX=3');
    }

    protected function tearDown(): void
    {
        putenv('RATE_LIMIT_MAX');
    }

    #[Test]
    #[TestDox('requests under the limit pass through')]
    public function allowsRequestsUnderTheLimit(): void
    {
        $middleware = new RateLimitMiddleware(new FakeRedis());
        $handler = $this->passthroughHandler();

        $response = $middleware->process($this->request(), $handler);

        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('returns 429 + Retry-After once the limit is exceeded')]
    public function returns429WithRetryAfterOnceLimitExceeded(): void
    {
        $middleware = new RateLimitMiddleware(new FakeRedis());
        $handler = $this->passthroughHandler();

        for ($i = 0; $i < 3; $i++) {
            $middleware->process($this->request(), $handler);
        }
        $response = $middleware->process($this->request(), $handler);

        self::assertSame(429, $response->getStatusCode());
        self::assertSame('60', $response->getHeaderLine('Retry-After'));
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame('rate_limited', $body['error']['code']);
    }

    private function request(): ServerRequestInterface
    {
        return new ServerRequestFactory()->createServerRequest('GET', '/', ['REMOTE_ADDR' => '127.0.0.1']);
    }

    private function passthroughHandler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new ResponseFactory()->createResponse(200);
            }
        };
    }
}
