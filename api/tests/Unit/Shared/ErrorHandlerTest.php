<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Exceptions\EventNotFoundException;
use App\Shared\ErrorHandler;
use App\Shared\RequestContext;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Psr7\Factory\ServerRequestFactory;

final class ErrorHandlerTest extends TestCase
{
    #[Test]
    #[TestDox('a DomainException maps to its own status/code and logs at warning')]
    public function domainExceptionMapsToItsOwnStatusAndCode(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');
        $logger->expects(self::never())->method('error');

        $requestContext = new RequestContext();
        $requestContext->setRequestId('req-1');
        $handler = new ErrorHandler($logger, $requestContext);

        $response = $handler(
            new ServerRequestFactory()->createServerRequest('GET', '/'),
            new EventNotFoundException('missing-id'),
            false,
            true,
            true,
        );

        self::assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame('event_not_found', $body['error']['code']);
        self::assertSame('req-1', $body['error']['request_id']);
    }

    #[Test]
    #[TestDox('a generic Throwable maps to a 500 with no leaked message, logged at error')]
    public function genericThrowableMapsToInternalErrorWithNoLeakedMessage(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $logger->expects(self::never())->method('warning');

        $handler = new ErrorHandler($logger, new RequestContext());

        $response = $handler(
            new ServerRequestFactory()->createServerRequest('GET', '/'),
            new RuntimeException('sensitive internal detail'),
            false,
            true,
            true,
        );

        self::assertSame(500, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame('internal_error', $body['error']['code']);
        self::assertStringNotContainsString('sensitive internal detail', (string) $response->getBody());
    }
}
