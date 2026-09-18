<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\HttpStatus;

final class InsufficientStockException extends DomainException
{
    public function __construct(string $priceId)
    {
        parent::__construct(sprintf('Not enough stock available for price "%s".', $priceId));
    }

    public function getStatus(): HttpStatus
    {
        return HttpStatus::Conflict;
    }

    public function getErrorCode(): string
    {
        return 'insufficient_stock';
    }
}
