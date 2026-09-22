<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class EventListControllerTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('lists only PUBLISHED events for the requested affiliate')]
    public function listsOnlyPublishedEventsForTheAffiliate(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $otherAffiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();

        $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate, 'status' => 'PUBLISHED', 'title' => 'Visible Show']);
        $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate, 'status' => 'DRAFT', 'title' => 'Hidden Draft']);
        $this->eventFactory->create(['venue' => $venue, 'affiliate' => $otherAffiliate, 'status' => 'PUBLISHED', 'title' => 'Other Affiliate Show']);
        $this->entityManager->flush();

        $response = $this->get("/api/{$affiliate->getId()->toString()}/events");

        self::assertSame(200, $response->status);
        self::assertCount(1, $response->json['data']);
        self::assertSame('Visible Show', $response->json['data'][0]['title']);
    }

    #[Test]
    #[TestDox('cursor pagination pages through results without duplicates')]
    public function cursorPaginationPagesThroughResults(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();

        for ($i = 0; $i < 5; $i++) {
            $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate]);
        }
        $this->entityManager->flush();

        $firstPage = $this->get("/api/{$affiliate->getId()->toString()}/events?limit=2");

        self::assertCount(2, $firstPage->json['data']);
        self::assertTrue($firstPage->json['meta']['has_more']);

        $secondPage = $this->get("/api/{$affiliate->getId()->toString()}/events?limit=2&cursor={$firstPage->json['meta']['next_cursor']}");

        self::assertCount(2, $secondPage->json['data']);
        $firstIds = array_column($firstPage->json['data'], 'id');
        $secondIds = array_column($secondPage->json['data'], 'id');
        self::assertEmpty(array_intersect($firstIds, $secondIds));
    }

    #[Test]
    #[TestDox('an unknown affiliate id returns 404 affiliate_not_found')]
    public function unknownAffiliateReturns404(): void
    {
        $response = $this->get('/api/00000000-0000-7000-8000-000000000000/events');

        self::assertSame(404, $response->status);
        self::assertSame('affiliate_not_found', $response->json['error']['code']);
    }

    #[Test]
    #[TestDox('an invalid category filter returns 400 invalid_request')]
    public function invalidCategoryFilterReturns400(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $this->entityManager->flush();

        $response = $this->get("/api/{$affiliate->getId()->toString()}/events?category=not-a-uuid");

        self::assertSame(400, $response->status);
        self::assertSame('invalid_request', $response->json['error']['code']);
    }
}
