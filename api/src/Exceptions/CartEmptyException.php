<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\HttpStatus;

final class CartEmptyException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cart is empty or missing.');
    }

    public function getStatus(): HttpStatus
    {
        return HttpStatus::BadRequest;
    }

    public function getErrorCode(): string
    {
        return 'cart_empty';
    }
}
