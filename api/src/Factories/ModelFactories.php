<?php

declare(strict_types=1);

namespace App\Factories;

/**
 * Bundles the per-entity factories for consumers (like FixtureEventCommand)
 * that need several — the max-4-constructor-params exception, see docs.
 */
final class ModelFactories
{
    public function __construct(
        public readonly AffiliateFactory $affiliate,
        public readonly CategoryFactory $category,
        public readonly VenueFactory $venue,
        public readonly EventFactory $event,
        public readonly AreaFactory $area,
        public readonly PriceFactory $price,
    ) {
    }
}
