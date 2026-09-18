<?php

declare(strict_types=1);

namespace Tests\Unit\Middlewares;

use App\Middlewares\RequestIdMiddleware;
use App\Shared\RequestContext;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

final class RequestIdMiddlewareTest extends TestCase
{
    #[Test]
    #[TestDox('generates a request id when the header is missing')]
    public function generatesARequestIdWhenHeaderIsMissing(): void
    {
        $requestContext = new RequestContext();
        $middleware = new RequestIdMiddleware($requestContext);

        $response = $middleware->process(
            new ServerRequestFactory()->createServerRequest('GET', '/'),
            $this->passthroughHandler(),
        );

        self::assertNotSame('', $response->getHeaderLine('X-Request-Id'));
        self::assertSame($response->getHeaderLine('X-Request-Id'), $requestContext->getRequestId());
    }

    #[Test]
    #[TestDox('reuses the incoming X-Request-Id header')]
    public function reusesTheIncomingRequestId(): void
    {
        $requestContext = new RequestContext();
        $middleware = new RequestIdMiddleware($requestContext);

        $request = new ServerRequestFactory()->createServerRequest('GET', '/')
            ->withHeader('X-Request-Id', 'incoming-id');

        $response = $middleware->process($request, $this->passthroughHandler());

        self::assertSame('incoming-id', $response->getHeaderLine('X-Request-Id'));
        self::assertSame('incoming-id', $requestContext->getRequestId());
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
