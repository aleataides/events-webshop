<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Resources\PriceResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Tests\Support\UsesFactories;

final class PriceResourceTest extends TestCase
{
    use UsesFactories;

    protected function setUp(): void
    {
        $this->setUpFactories();
    }

    #[Test]
    #[TestDox('converts cents to decimal-string major units')]
    public function convertsCentsToDecimalMajorUnits(): void
    {
        $event = $this->eventFactory->make(['venue' => $this->venueFactory->make(), 'affiliate' => $this->affiliateFactory->make()]);
        $area = $this->areaFactory->make(['event' => $event]);
        $price = $this->priceFactory->make([
            'area' => $area,
            'basePriceCents' => 2200,
            'ticketFeeCents' => 176,
            'outletFeeCents' => 0,
        ]);

        $result = new PriceResource($price)->jsonSerialize();

        self::assertSame('22.00', $result['basePrice']);
        self::assertSame('1.76', $result['ticketFee']);
        self::assertSame('0.00', $result['outletFee']);
        self::assertSame('23.76', $result['value']);
    }
}
