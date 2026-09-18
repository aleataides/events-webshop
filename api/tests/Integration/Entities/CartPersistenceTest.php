<?php

declare(strict_types=1);

namespace Tests\Integration\Entities;

use App\Entities\Cart;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class CartPersistenceTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('a cart with reservations round-trips through the database')]
    public function cartWithReservationsRoundTripsThroughTheDatabase(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event]);
        $price = $this->priceFactory->create(['area' => $area]);

        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 2]);
        $this->entityManager->flush();
        $cartId = $cart->getId();
        $this->entityManager->clear();

        $reloaded = $this->entityManager->find(Cart::class, $cartId);

        self::assertNotNull($reloaded);
        self::assertCount(1, $reloaded->getReservations());
        self::assertSame(2, $reloaded->getReservations()->first()->getQty());
    }
}
