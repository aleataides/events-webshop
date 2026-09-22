<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\IntegrationTestCase;

final class CategoryListControllerTest extends IntegrationTestCase
{
    #[Test]
    #[TestDox('only returns categories with at least one PUBLISHED event for the affiliate')]
    public function onlyReturnsCategoriesWithAPublishedEvent(): void
    {
        $affiliate = $this->affiliateFactory->create();
        $venue = $this->venueFactory->create();

        $usedCategory = $this->categoryFactory->create(['name' => 'Konzert']);
        $this->categoryFactory->create(['name' => 'Sport']);
        $draftOnlyCategory = $this->categoryFactory->create(['name' => 'Theater']);

        $publishedEvent = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate, 'status' => 'PUBLISHED']);
        $publishedEvent->addCategory($usedCategory);

        $draftEvent = $this->eventFactory->create(['venue' => $venue, 'affiliate' => $affiliate, 'status' => 'DRAFT']);
        $draftEvent->addCategory($draftOnlyCategory);

        $this->entityManager->flush();

        $response = $this->get("/api/{$affiliate->getId()->toString()}/categories");

        self::assertSame(200, $response->status);
        $names = array_column($response->json['data'], 'name');
        self::assertSame(['Konzert'], $names);
        self::assertNotContains('Sport', $names);
        self::assertNotContains('Theater', $names);
    }
}
