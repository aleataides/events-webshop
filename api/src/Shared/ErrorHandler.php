<?php

declare(strict_types=1);

namespace App\Shared;

use App\Enums\ErrorCode;
use App\Enums\HttpStatus;
use App\Exceptions\DomainException;
use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
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
        /** @var array{0: HttpStatus, 1: ErrorCode, 2: string, 3: bool} $resolved */
        $resolved = match (true) {
            $exception instanceof DomainException
                => [$exception->getStatus(), $exception->getErrorCode(), $exception->getMessage(), true],
            $exception instanceof HttpNotFoundException
                => [HttpStatus::NotFound, ErrorCode::NotFound, $exception->getMessage(), true],
            $exception instanceof HttpMethodNotAllowedException
                => [HttpStatus::MethodNotAllowed, ErrorCode::MethodNotAllowed, $exception->getMessage(), true],
            default => [HttpStatus::InternalServerError, ErrorCode::InternalError, 'An unexpected error occurred.', false],
        };
        [$status, $code, $message, $expected] = $resolved;

        if ($expected) {
            $this->logger->warning($message, ['code' => $code->value]);
        } else {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }

        return new JsonResponse([
            'error' => [
                'code' => $code->value,
                'message' => $message,
                'request_id' => $this->requestContext->getRequestId(),
            ],
        ], $status->value);
    }
}
