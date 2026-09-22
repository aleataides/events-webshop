<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Exceptions\InvalidRequestException;
use App\Shared\ValidatesQueryParams;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

final class ValidatesQueryParamsTest extends TestCase
{
    private object $subject;

    protected function setUp(): void
    {
        $this->subject = new class () {
            use ValidatesQueryParams;

            public function parseUuid(?string $value): ?UuidInterface
            {
                return $this->parseUuidParam($value, 'param');
            }

            public function parseDate(?string $value): ?DateTimeImmutable
            {
                return $this->parseDateParam($value, 'param');
            }
        };
    }

    #[Test]
    #[TestDox('parseUuidParam returns null for empty input')]
    public function parseUuidParamReturnsNullForEmptyInput(): void
    {
        self::assertNull($this->subject->parseUuid(null));
        self::assertNull($this->subject->parseUuid(''));
    }

    #[Test]
    #[TestDox('parseUuidParam returns a Uuid for valid input')]
    public function parseUuidParamReturnsUuidForValidInput(): void
    {
        $result = $this->subject->parseUuid('01a0b4b7-5497-70b5-8c16-f494c62af95b');

        self::assertSame('01a0b4b7-5497-70b5-8c16-f494c62af95b', $result?->toString());
    }

    #[Test]
    #[TestDox('parseUuidParam throws InvalidRequestException for invalid input')]
    public function parseUuidParamThrowsForInvalidInput(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->subject->parseUuid('not-a-uuid');
    }

    #[Test]
    #[TestDox('parseDateParam returns null for empty input')]
    public function parseDateParamReturnsNullForEmptyInput(): void
    {
        self::assertNull($this->subject->parseDate(null));
        self::assertNull($this->subject->parseDate(''));
    }

    #[Test]
    #[TestDox('parseDateParam throws InvalidRequestException for invalid input')]
    public function parseDateParamThrowsForInvalidInput(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->subject->parseDate('not-a-date');
    }
}
