<?php

declare(strict_types=1);

namespace App\Shared;

use App\Exceptions\InvalidRequestException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait ValidatesQueryParams
{
    private function parseUuidParam(?string $value, string $paramName): ?UuidInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Uuid::fromString($value);
        } catch (InvalidUuidStringException) {
            throw new InvalidRequestException(sprintf('"%s" must be a valid UUID.', $paramName));
        }
    }

    private function parseDateParam(?string $value, string $paramName): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value, new DateTimeZone('UTC'));
        } catch (Exception) {
            throw new InvalidRequestException(sprintf('"%s" must be a valid date.', $paramName));
        }
    }
}
