<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ErrorCode;
use App\Enums\HttpStatus;

final class AffiliateNotFoundException extends DomainException
{
    public function __construct(string $affiliateId)
    {
        parent::__construct(sprintf('Affiliate "%s" not found.', $affiliateId));
    }

    public function getStatus(): HttpStatus
    {
        return HttpStatus::NotFound;
    }

    public function getErrorCode(): ErrorCode
    {
        return ErrorCode::AffiliateNotFound;
    }
}
