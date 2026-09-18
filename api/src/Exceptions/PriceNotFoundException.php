<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\HttpStatus;

final class PriceNotFoundException extends DomainException
{
    public function __construct(string $priceId)
    {
        parent::__construct(sprintf('Price "%s" not found.', $priceId));
    }

    public function getStatus(): HttpStatus
    {
        return HttpStatus::NotFound;
    }

    public function getErrorCode(): string
    {
        return 'price_not_found';
    }
}
