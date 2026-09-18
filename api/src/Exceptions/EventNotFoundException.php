<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\HttpStatus;

final class EventNotFoundException extends DomainException
{
    public function __construct(string $eventId)
    {
        parent::__construct(sprintf('Event "%s" not found.', $eventId));
    }

    public function getStatus(): HttpStatus
    {
        return HttpStatus::NotFound;
    }

    public function getErrorCode(): string
    {
        return 'event_not_found';
    }
}
