<?php

declare(strict_types=1);

namespace App\Shared;

/**
 * Minor-unit (cents) <-> major-unit conversion, avoids decimal/float precision issues.
 */
trait MoneyConvertible
{
    private function toMinorUnits(string $majorUnits): int
    {
        return (int) round((float) $majorUnits * 100);
    }

    private function toMajorUnits(int $minorUnits): string
    {
        return number_format($minorUnits / 100, 2, '.', '');
    }
}
