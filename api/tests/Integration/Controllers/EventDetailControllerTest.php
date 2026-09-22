<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class EventDetailControllerTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('returns the full event payload incl. areas/prices')]
    public function returnsFullEventPayload(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate, 'title' => 'Detail Show']);
        $area = $this->areaFactory->create(['event' => $event, 'name' => 'Innenraum', 'capacity' => 50]);
        $this->priceFactory->create(['area' => $area, 'name' => 'Normalpreis', 'basePriceCents' => 2000]);
        $this->entityManager->flush();

        $response = $this->get("/api/{$affiliate->getId()->toString()}/events/{$event->getId()->toString()}");

        self::assertSame(200, $response->status);
        self::assertSame('Detail Show', $response->json['data']['title']);
        self::assertCount(1, $response->json['data']['areas']);
        self::assertSame('Innenraum', $response->json['data']['areas'][0]['name']);
    }

    #[Test]
    #[TestDox('an unknown event id returns 404 event_not_found')]
    public function unknownEventReturns404(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $this->entityManager->flush();

        $response = $this->get("/api/{$affiliate->getId()->toString()}/events/00000000-0000-7000-8000-000000000000");

        self::assertSame(404, $response->status);
        self::assertSame('event_not_found', $response->json['error']['code']);
    }

    #[Test]
    #[TestDox('a non-UUID event id returns 400 invalid_request')]
    public function nonUuidEventIdReturns400(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $this->entityManager->flush();

        $response = $this->get("/api/{$affiliate->getId()->toString()}/events/not-a-uuid");

        self::assertSame(400, $response->status);
        self::assertSame('invalid_request', $response->json['error']['code']);
    }

    #[Test]
    #[TestDox('an event belonging to a different affiliate returns 404 (affiliate scoping)')]
    public function eventFromDifferentAffiliateReturns404(): void
    {
        $owner = $this->affiliateFactory->create();
        $requester = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();
        $event = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $owner]);
        $this->entityManager->flush();

        $response = $this->get("/api/{$requester->getId()->toString()}/events/{$event->getId()->toString()}");

        self::assertSame(404, $response->status);
    }
}
