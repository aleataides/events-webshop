<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Entities\Area;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class CartItemStoreControllerTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('with no X-Cart-Id, lazily creates a cart and returns it in the body')]
    public function noCartIdLazilyCreatesACart(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10]);
        $price = $this->priceFactory->create(['area' => $area, 'basePriceCents' => 1000, 'ticketFeeCents' => 0, 'outletFeeCents' => 0]);
        $this->entityManager->flush();

        $response = $this->post(
            "/api/{$affiliate->getId()->toString()}/cart/items",
            ['priceId' => $price->getId()->toString(), 'qty' => 2],
        );

        self::assertSame(201, $response->status);
        self::assertNotEmpty($response->json['data']['id']);
        self::assertSame(2, $response->json['data']['items'][0]['qty']);
        self::assertSame('20.00', $response->json['data']['total']);

        $this->entityManager->clear();
        $reloadedArea = $this->entityManager->find(Area::class, $area->getId());
        self::assertSame(2, $reloadedArea->getReservedQty());
    }

    #[Test]
    #[TestDox('adding to an existing cart reuses it and renews expiry')]
    public function addingToExistingCartReusesItAndRenewsExpiry(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10]);
        $price = $this->priceFactory->create(['area' => $area]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $this->entityManager->flush();

        $response = $this->post(
            "/api/{$affiliate->getId()->toString()}/cart/items",
            ['priceId' => $price->getId()->toString(), 'qty' => 1],
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(201, $response->status);
        self::assertSame($cart->getId()->toString(), $response->json['data']['id']);
    }

    #[Test]
    #[TestDox('rejects when there is not enough stock available')]
    public function rejectsWhenNotEnoughStockAvailable(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 2]);
        $price = $this->priceFactory->create(['area' => $area]);
        $this->entityManager->flush();

        $response = $this->post(
            "/api/{$affiliate->getId()->toString()}/cart/items",
            ['priceId' => $price->getId()->toString(), 'qty' => 3],
        );

        self::assertSame(409, $response->status);
        self::assertSame('insufficient_stock', $response->json['error']['code']);
    }

    #[Test]
    #[TestDox('rejects an unknown priceId with 404')]
    public function rejectsUnknownPriceId(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $this->entityManager->flush();

        $response = $this->post(
            "/api/{$affiliate->getId()->toString()}/cart/items",
            ['priceId' => '00000000-0000-7000-8000-000000000000', 'qty' => 1],
        );

        self::assertSame(404, $response->status);
        self::assertSame('price_not_found', $response->json['error']['code']);
    }

    #[Test]
    #[TestDox('rejects qty less than 1 with 400')]
    public function rejectsQtyLessThanOne(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event]);
        $price = $this->priceFactory->create(['area' => $area]);
        $this->entityManager->flush();

        $response = $this->post(
            "/api/{$affiliate->getId()->toString()}/cart/items",
            ['priceId' => $price->getId()->toString(), 'qty' => 0],
        );

        self::assertSame(400, $response->status);
        self::assertSame('invalid_request', $response->json['error']['code']);
    }
}
