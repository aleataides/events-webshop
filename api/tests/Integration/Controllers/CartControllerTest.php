<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Entities\Area;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class CartControllerTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('no X-Cart-Id header returns a null cart, not an error')]
    public function noCartIdReturnsNullCart(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $this->entityManager->flush();

        $response = $this->get("/api/{$affiliate->getId()->toString()}/cart");

        self::assertSame(200, $response->status);
        self::assertNull($response->json['data']);
    }

    #[Test]
    #[TestDox('an unknown/invalid X-Cart-Id is treated the same as no cart')]
    public function unknownCartIdReturnsNullCart(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $this->entityManager->flush();

        $response = $this->get(
            "/api/{$affiliate->getId()->toString()}/cart",
            ['X-Cart-Id' => '00000000-0000-7000-8000-000000000000'],
        );

        self::assertSame(200, $response->status);
        self::assertNull($response->json['data']);
    }

    #[Test]
    #[TestDox('a valid non-expired cart returns its items and total')]
    public function validCartReturnsItemsAndTotal(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event]);
        $price = $this->priceFactory->create(['area' => $area, 'basePriceCents' => 1000, 'ticketFeeCents' => 0, 'outletFeeCents' => 0]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 3]);
        $this->entityManager->flush();

        $response = $this->get(
            "/api/{$affiliate->getId()->toString()}/cart",
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(200, $response->status);
        self::assertCount(1, $response->json['data']['items']);
        self::assertSame(3, $response->json['data']['items'][0]['qty']);
        self::assertSame('30.00', $response->json['data']['total']);
    }

    #[Test]
    #[TestDox('an expired cart releases its reservations and returns 410 cart_expired')]
    public function expiredCartReleasesReservationsAndReturns410(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 3]);
        $price = $this->priceFactory->create(['area' => $area]);
        $past = new DateTimeImmutable('-1 minute', new DateTimeZone('UTC'));
        $cart = $this->cartFactory->create(['affiliate' => $affiliate, 'expiresAt' => $past]);
        $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 3]);
        $this->entityManager->flush();

        $response = $this->get(
            "/api/{$affiliate->getId()->toString()}/cart",
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(410, $response->status);
        self::assertSame('cart_expired', $response->json['error']['code']);

        $this->entityManager->clear();
        $reloadedArea = $this->entityManager->find(Area::class, $area->getId());
        self::assertSame(0, $reloadedArea->getReservedQty());
    }
}
