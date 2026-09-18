<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Shared\HttpStatus;

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

    public function getErrorCode(): string
    {
        return 'affiliate_not_found';
    }
}
