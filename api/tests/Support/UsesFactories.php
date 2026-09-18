<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Factories\AffiliateFactory;
use App\Factories\AreaFactory;
use App\Factories\CategoryFactory;
use App\Factories\EventFactory;
use App\Factories\PriceFactory;
use App\Factories\VenueFactory;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;

/**
 * Wires App\Factories\* for tests. Pass a stub EntityManager for Unit tests
 * that only call make(); pass RefreshDatabase's real one to persist/create().
 */
trait UsesFactories
{
    protected AffiliateFactory $affiliateFactory;
    protected VenueFactory $venueFactory;
    protected CategoryFactory $categoryFactory;
    protected EventFactory $eventFactory;
    protected AreaFactory $areaFactory;
    protected PriceFactory $priceFactory;

    protected function setUpFactories(?EntityManagerInterface $entityManager = null): void
    {
        $faker = FakerFactory::create();
        $entityManager ??= $this->createStub(EntityManagerInterface::class);

        $this->affiliateFactory = new AffiliateFactory($faker, $entityManager);
        $this->venueFactory = new VenueFactory($faker, $entityManager);
        $this->categoryFactory = new CategoryFactory($faker, $entityManager);
        $this->eventFactory = new EventFactory($faker, $entityManager);
        $this->areaFactory = new AreaFactory($faker, $entityManager);
        $this->priceFactory = new PriceFactory($faker, $entityManager);
    }
}
