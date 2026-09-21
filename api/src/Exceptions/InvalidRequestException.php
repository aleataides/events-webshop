<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ErrorCode;
use App\Enums\HttpStatus;

final class InvalidRequestException extends DomainException
{
    public function getStatus(): HttpStatus
    {
        return HttpStatus::BadRequest;
    }

    public function getErrorCode(): ErrorCode
    {
        return ErrorCode::InvalidRequest;
    }
}
