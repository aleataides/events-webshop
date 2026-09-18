<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Shared\HttpStatus;
use RuntimeException;

/**
 * Base for exceptions the ErrorHandler maps to a specific HTTP status + stable
 * error code, logged at warning (expected business outcome, not a bug).
 */
abstract class DomainException extends RuntimeException
{
    abstract public function getStatus(): HttpStatus;

    abstract public function getErrorCode(): string;
}
