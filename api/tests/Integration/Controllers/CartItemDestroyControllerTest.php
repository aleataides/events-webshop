<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Entities\Area;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class CartItemDestroyControllerTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('removes the item and releases its stock')]
    public function removesItemAndReleasesStock(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        $area = $this->areaFactory->create(['event' => $event, 'capacity' => 10, 'reservedQty' => 4]);
        $price = $this->priceFactory->create(['area' => $area]);
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $reservation = $this->ticketReservationFactory->create(['cart' => $cart, 'price' => $price, 'qty' => 4]);
        $this->entityManager->flush();

        $response = $this->delete(
            "/api/{$affiliate->getId()->toString()}/cart/items/{$reservation->getId()->toString()}",
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(200, $response->status);
        self::assertCount(0, $response->json['data']['items']);

        $this->entityManager->clear();
        $reloadedArea = $this->entityManager->find(Area::class, $area->getId());
        self::assertSame(0, $reloadedArea->getReservedQty());
    }

    #[Test]
    #[TestDox('an unknown item id returns 404 cart_item_not_found')]
    public function unknownItemIdReturns404(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $cart = $this->cartFactory->create(['affiliate' => $affiliate]);
        $this->entityManager->flush();

        $response = $this->delete(
            "/api/{$affiliate->getId()->toString()}/cart/items/00000000-0000-7000-8000-000000000000",
            ['X-Cart-Id' => $cart->getId()->toString()],
        );

        self::assertSame(404, $response->status);
        self::assertSame('cart_item_not_found', $response->json['error']['code']);
    }
}
