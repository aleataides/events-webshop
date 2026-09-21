<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ErrorCode;
use App\Enums\HttpStatus;

final class TicketReservationNotFoundException extends DomainException
{
    public function __construct(string $reservationId)
    {
        parent::__construct(sprintf('Cart item "%s" not found.', $reservationId));
    }

    public function getStatus(): HttpStatus
    {
        return HttpStatus::NotFound;
    }

    public function getErrorCode(): ErrorCode
    {
        return ErrorCode::TicketReservationNotFound;
    }
}
