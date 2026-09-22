<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Entities\Area;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class CartBuyControllerTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('converts reservations into an order and finalizes stock')]
    public function convertsReservationsIntoAnOrder(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 3]);
        $price = $this->priceFactory->create(['area' => $area, 'basePriceCents' => 1000, 'ticketFeeCents' => 0, 'outletFeeCents' => 0]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 3]);
        $this->entityManager->flush();

        $response = $this->post(
            "/api/{$affiliate->getId()->toString()}/cart/buy",
            [],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(201, $response->status);
        self::assertNotEmpty($response->json['data']['id']);
        self::assertSame(3, $response->json['data']['items'][0]['qty']);
        self::assertSame('30.00', $response->json['data']['total']);

        $this->entityManager->clear();
        $reloadedArea = $this->entityManager->find(Area::class, $area->getId());
        self::assertSame(0, $reloadedArea->getReservedQty());
        self::assertSame(3, $reloadedArea->getSoldQty());
    }

    #[Test]
    #[TestDox('rejects buy with 409 insufficient_stock when a reservation was already converted (concurrent Buy)')]
    public function rejectsBuyWhenReservationAlreadyConverted(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        // reservedQty (1) below the reservation's qty (3) simulates a concurrent Buy having already claimed it.
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 1]);
        $price = $this->priceFactory->create(['area' => $area, 'basePriceCents' => 1000, 'ticketFeeCents' => 0, 'outletFeeCents' => 0]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 3]);
        $this->entityManager->flush();

        $response = $this->post(
            "/api/{$affiliate->getId()->toString()}/cart/buy",
            [],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(409, $response->status);
        self::assertSame('insufficient_stock', $response->json['error']['code']);

        $this->entityManager->clear();
        $reloadedArea = $this->entityManager->find(Area::class, $area->getId());
        self::assertSame(1, $reloadedArea->getReservedQty());
        self::assertSame(0, $reloadedArea->getSoldQty());
    }

    #[Test]
    #[TestDox('rejects buying with no cart with 400 cart_empty')]
    public function rejectsBuyingWithNoCart(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $this->entityManager->flush();

        $response = $this->post("/api/{$affiliate->getId()->toString()}/cart/buy");

        self::assertSame(400, $response->status);
        self::assertSame('cart_empty', $response->json['error']['code']);
    }

    #[Test]
    #[TestDox('rejects buying an empty cart with 400 cart_empty')]
    public function rejectsBuyingAnEmptyCart(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $this->entityManager->flush();

        $response = $this->post(
            "/api/{$affiliate->getId()->toString()}/cart/buy",
            [],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(400, $response->status);
        self::assertSame('cart_empty', $response->json['error']['code']);
    }
}
