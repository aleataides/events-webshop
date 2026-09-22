<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Resources\EventListResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Tests\Support\UsesFactories;

final class EventListResourceTest extends TestCase
{
    use UsesFactories;

    private object $venue;
    private object $affiliate;

    protected function setUp(): void
    {
        $this->setUpFactories();
        $this->venue = $this->venueFactory->make();
        $this->affiliate = $this->affiliateFactory->make();
    }

    #[Test]
    #[TestDox('soldout is true when no area has any availability')]
    public function soldoutTrueWhenNoAreaAvailable(): void
    {
        $event = $this->eventFactory->make(['venue' => $this->venue, 'affiliate' => $this->affiliate]);
        $area = $this->areaFactory->make(['event' => $event, 'capacity' => 10, 'reservedQty' => 4, 'soldQty' => 6]);
        $this->priceFactory->make(['area' => $area, 'basePriceCents' => 2000]);

        $result = new EventListResource($event)->jsonSerialize();

        self::assertTrue($result['soldout']);
    }

    #[Test]
    #[TestDox('soldout is false when any area still has availability')]
    public function soldoutFalseWhenAreaAvailable(): void
    {
        $event = $this->eventFactory->make(['venue' => $this->venue, 'affiliate' => $this->affiliate]);
        $area = $this->areaFactory->make(['event' => $event, 'capacity' => 10, 'reservedQty' => 2, 'soldQty' => 2]);
        $this->priceFactory->make(['area' => $area, 'basePriceCents' => 2000]);

        $result = new EventListResource($event)->jsonSerialize();

        self::assertFalse($result['soldout']);
    }

    #[Test]
    #[TestDox('min/max price span across all of the event\'s areas')]
    public function minMaxPriceSpansAllAreas(): void
    {
        $event = $this->eventFactory->make(['venue' => $this->venue, 'affiliate' => $this->affiliate]);

        $cheapArea = $this->areaFactory->make(['event' => $event, 'capacity' => 10]);
        $this->priceFactory->make(['area' => $cheapArea, 'basePriceCents' => 1000, 'ticketFeeCents' => 0, 'outletFeeCents' => 0]);

        $pricyArea = $this->areaFactory->make(['event' => $event, 'capacity' => 10]);
        $this->priceFactory->make(['area' => $pricyArea, 'basePriceCents' => 5000, 'ticketFeeCents' => 0, 'outletFeeCents' => 0]);

        $result = new EventListResource($event)->jsonSerialize();

        self::assertSame('10.00', $result['minPrice']);
        self::assertSame('50.00', $result['maxPrice']);
    }

    #[Test]
    #[TestDox('min/max price are null when the event has no areas')]
    public function priceIsNullWithoutAreas(): void
    {
        $event = $this->eventFactory->make(['venue' => $this->venue, 'affiliate' => $this->affiliate]);

        $result = new EventListResource($event)->jsonSerialize();

        self::assertNull($result['minPrice']);
        self::assertNull($result['maxPrice']);
        self::assertFalse($result['soldout']);
    }
}
