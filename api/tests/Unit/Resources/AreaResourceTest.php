<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Resources\AreaResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Tests\Support\UsesFactories;

final class AreaResourceTest extends TestCase
{
    use UsesFactories;

    protected function setUp(): void
    {
        $this->setUpFactories();
    }

    #[Test]
    #[TestDox('available is clamped to zero when oversold')]
    public function availableClampedToZeroWhenOversold(): void
    {
        $event = $this->eventFactory->make(['venue' => $this->venueFactory->make(), 'affiliate' => $this->affiliateFactory->make()]);
        $area = $this->areaFactory->make(['event' => $event, 'capacity' => 10, 'reservedQty' => 6, 'soldQty' => 6]);

        $result = new AreaResource($area)->toArray();

        self::assertSame(0, $result['available']);
    }

    #[Test]
    #[TestDox('available reflects remaining capacity')]
    public function availableReflectsRemainingCapacity(): void
    {
        $event = $this->eventFactory->make(['venue' => $this->venueFactory->make(), 'affiliate' => $this->affiliateFactory->make()]);
        $area = $this->areaFactory->make(['event' => $event, 'capacity' => 10, 'reservedQty' => 2, 'soldQty' => 3]);

        $result = new AreaResource($area)->toArray();

        self::assertSame(5, $result['available']);
    }
}
