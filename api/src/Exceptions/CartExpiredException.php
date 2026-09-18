<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\HttpStatus;

final class CartExpiredException extends DomainException
{
    public function __construct(string $cartId)
    {
        parent::__construct(sprintf('Cart "%s" has expired.', $cartId));
    }

    public function getStatus(): HttpStatus
    {
        return HttpStatus::Gone;
    }

    public function getErrorCode(): string
    {
        return 'cart_expired';
    }
}
