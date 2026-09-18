<?php

declare(strict_types=1);

namespace App\Shared;

use App\Exceptions\DomainException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;
use Throwable;

/**
 * Slim's default error handler: maps DomainException subclasses to their
 * status + code, everything else to a generic 500 with no leaked details.
 */
final class ErrorHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RequestContext $requestContext,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        if ($exception instanceof DomainException) {
            $status = $exception->getStatus();
            $code = $exception->getErrorCode();
            $message = $exception->getMessage();
            $this->logger->warning($exception->getMessage(), ['code' => $code]);
        } else {
            $status = HttpStatus::InternalServerError;
            $code = 'internal_error';
            $message = 'An unexpected error occurred.';
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }

        $response = new Response($status->value);
        $response->getBody()->write(json_encode([
            'error' => [
                'code' => $code,
                'message' => $message,
                'request_id' => $this->requestContext->getRequestId(),
            ],
        ], JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
