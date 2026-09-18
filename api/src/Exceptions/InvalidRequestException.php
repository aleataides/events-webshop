<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Shared\HttpStatus;

final class InvalidRequestException extends DomainException
{
    public function getStatus(): HttpStatus
    {
        return HttpStatus::BadRequest;
    }

    public function getErrorCode(): string
    {
        return 'invalid_request';
    }
}
