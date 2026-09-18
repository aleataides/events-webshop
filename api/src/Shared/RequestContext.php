<?php

declare(strict_types=1);

namespace App\Shared;

/**
 * Per-request mutable holder for request_id/affiliate_id, set by middleware
 * early in the pipeline, read by the logger processor and ErrorHandler.
 */
final class RequestContext
{
    private ?string $requestId = null;

    private ?string $affiliateId = null;

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function getAffiliateId(): ?string
    {
        return $this->affiliateId;
    }

    public function setAffiliateId(string $affiliateId): void
    {
        $this->affiliateId = $affiliateId;
    }
}
