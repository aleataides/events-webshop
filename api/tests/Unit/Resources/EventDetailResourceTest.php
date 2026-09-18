<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Resources\EventDetailResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Tests\Support\UsesFactories;

final class EventDetailResourceTest extends TestCase
{
    use UsesFactories;

    protected function setUp(): void
    {
        $this->setUpFactories();
    }

    #[Test]
    #[TestDox('detail payload includes description, priceInfo, and nested areas/prices')]
    public function includesDescriptionPriceInfoAndAreas(): void
    {
        $event = $this->eventFactory->make([
            'venue' => $this->venueFactory->make(),
            'affiliate' => $this->affiliateFactory->make(),
            'description' => '<p>Full description.</p>',
            'priceInfo' => 'VVK ab 10 Euro',
        ]);
        $area = $this->areaFactory->make(['event' => $event, 'name' => 'Innenraum', 'capacity' => 50]);
        $this->priceFactory->make(['area' => $area, 'name' => 'Normalpreis', 'basePriceCents' => 2000]);

        $result = new EventDetailResource($event)->toArray();

        self::assertSame('<p>Full description.</p>', $result['description']);
        self::assertSame('VVK ab 10 Euro', $result['priceInfo']);
        self::assertCount(1, $result['areas']);
        self::assertSame('Innenraum', $result['areas'][0]['name']);
        self::assertSame('Normalpreis', $result['areas'][0]['prices'][0]['name']);
    }
}
