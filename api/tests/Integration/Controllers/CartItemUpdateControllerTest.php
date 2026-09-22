<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Entities\Area;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class CartItemUpdateControllerTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('increasing qty reserves the extra stock')]
    public function increasingQtyReservesExtraStock(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 2]);
        $price = $this->priceFactory->create(['area' => $area]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $reservation = $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 2]);
        $this->entityManager->flush();

        $response = $this->patch(
            "/api/{$affiliate->getId()->toString()}/cart/items/{$reservation->getId()->toString()}",
            ['qty' => 5],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(200, $response->status);
        self::assertSame(5, $response->json['data']['items'][0]['qty']);

        $this->entityManager->clear();
        $reloadedArea = $this->entityManager->find(Area::class, $area->getId());
        self::assertSame(5, $reloadedArea->getReservedQty());
    }

    #[Test]
    #[TestDox('decreasing qty releases the difference')]
    public function decreasingQtyReleasesDifference(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 5]);
        $price = $this->priceFactory->create(['area' => $area]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $reservation = $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 5]);
        $this->entityManager->flush();

        $response = $this->patch(
            "/api/{$affiliate->getId()->toString()}/cart/items/{$reservation->getId()->toString()}",
            ['qty' => 2],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(200, $response->status);

        $this->entityManager->clear();
        $reloadedArea = $this->entityManager->find(Area::class, $area->getId());
        self::assertSame(2, $reloadedArea->getReservedQty());
    }

    #[Test]
    #[TestDox('qty=0 removes the item and releases all of its stock')]
    public function qtyZeroRemovesItemAndReleasesStock(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 3]);
        $price = $this->priceFactory->create(['area' => $area]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $reservation = $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 3]);
        $this->entityManager->flush();

        $response = $this->patch(
            "/api/{$affiliate->getId()->toString()}/cart/items/{$reservation->getId()->toString()}",
            ['qty' => 0],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(200, $response->status);
        self::assertCount(0, $response->json['data']['items']);

        $this->entityManager->clear();
        $reloadedArea = $this->entityManager->find(Area::class, $area->getId());
        self::assertSame(0, $reloadedArea->getReservedQty());
    }

    #[Test]
    #[TestDox('rejects increasing qty beyond available stock')]
    public function rejectsIncreasingQtyBeyondAvailableStock(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 3, 'reservedQty' => 2]);
        $price = $this->priceFactory->create(['area' => $area]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $reservation = $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 2]);
        $this->entityManager->flush();

        $response = $this->patch(
            "/api/{$affiliate->getId()->toString()}/cart/items/{$reservation->getId()->toString()}",
            ['qty' => 10],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(409, $response->status);
        self::assertSame('insufficient_stock', $response->json['error']['code']);
    }

    #[Test]
    #[TestDox('renews the cart expiry on a qty increase')]
    public function renewsCartExpiryOnQtyIncrease(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 2]);
        $price = $this->priceFactory->create(['area' => $area]);
        $expiresAt = new DateTimeImmutable('+5 minutes', new DateTimeZone('UTC'));
        $cart = $this->cartFactory->create(['affiliate' => $affiliate, 'expiresAt' => $expiresAt]);
        $reservation = $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 2]);
        $this->entityManager->flush();

        $response = $this->patch(
            "/api/{$affiliate->getId()->toString()}/cart/items/{$reservation->getId()->toString()}",
            ['qty' => 5],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(200, $response->status);
        self::assertGreaterThan($expiresAt->format('c'), $response->json['data']['expiresAt']);
    }

    #[Test]
    #[TestDox('does not renew the cart expiry on a qty decrease')]
    public function doesNotRenewCartExpiryOnQtyDecrease(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 5]);
        $price = $this->priceFactory->create(['area' => $area]);
        $expiresAt = new DateTimeImmutable('+5 minutes', new DateTimeZone('UTC'));
        $cart = $this->cartFactory->create(['affiliate' => $affiliate, 'expiresAt' => $expiresAt]);
        $reservation = $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 5]);
        $this->entityManager->flush();

        $response = $this->patch(
            "/api/{$affiliate->getId()->toString()}/cart/items/{$reservation->getId()->toString()}",
            ['qty' => 2],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(200, $response->status);
        self::assertSame($expiresAt->format('c'), $response->json['data']['expiresAt']);
    }

    #[Test]
    #[TestDox('an unknown item id returns 404 cart_item_not_found')]
    public function unknownItemIdReturns404(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $this->entityManager->flush();

        $response = $this->patch(
            "/api/{$affiliate->getId()->toString()}/cart/items/00000000-0000-7000-8000-000000000000",
            ['qty' => 1],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(404, $response->status);
        self::assertSame('cart_item_not_found', $response->json['error']['code']);
    }

    #[Test]
    #[TestDox('rejects a non-int qty with 400')]
    public function rejectsNonIntQty(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 2]);
        $price = $this->priceFactory->create(['area' => $area]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $reservation = $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 2]);
        $this->entityManager->flush();

        $response = $this->patch(
            "/api/{$affiliate->getId()->toString()}/cart/items/{$reservation->getId()->toString()}",
            ['qty' => '5'],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(400, $response->status);
        self::assertSame('invalid_request', $response->json['error']['code']);
    }
}
