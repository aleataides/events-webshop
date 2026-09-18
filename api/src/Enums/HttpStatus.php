<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Only the statuses this API actually returns — not a full RFC enumeration.
 */
enum HttpStatus: int
{
    case BadRequest = 400;
    case NotFound = 404;
    case TooManyRequests = 429;
    case InternalServerError = 500;
}
