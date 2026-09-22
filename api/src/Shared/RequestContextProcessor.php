<?php

declare(strict_types=1);

namespace App\Shared;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class RequestContextProcessor implements ProcessorInterface
{
    public function __construct(private readonly RequestContext $requestContext)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['request_id'] = $this->requestContext->getRequestId();
        $record->extra['affiliate_id'] = $this->requestContext->getAffiliateId();

        return $record;
    }
}
