<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Affiliate;
use App\Entities\Event;
use App\Repositories\EventRepository;
use App\Resources\EventListResource;
use DateTimeImmutable;
use Predis\ClientInterface;
use Ramsey\Uuid\UuidInterface;

final class EventService
{
    private const int LIST_CACHE_TTL_SECONDS = 60;

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ClientInterface $redis,
    ) {
    }

    /**
     * @return array{items: list<array<string, mixed>>, nextCursor: ?string, hasMore: bool}
     */
    public function listPublished(
        Affiliate $affiliate,
        ?string $search,
        ?UuidInterface $categoryId,
        ?DateTimeImmutable $dateFrom,
        ?DateTimeImmutable $dateTo,
        ?UuidInterface $cursor,
        int $limit,
    ): array {
        $cacheKey = $this->buildListCacheKey($affiliate, $search, $categoryId, $dateFrom, $dateTo, $cursor, $limit);

        $cached = $this->redis->get($cacheKey);
        if (is_string($cached)) {
            /** @var array{items: list<array<string, mixed>>, nextCursor: ?string, hasMore: bool} $decoded */
            $decoded = json_decode($cached, true, flags: JSON_THROW_ON_ERROR);

            return $decoded;
        }

        $found = $this->eventRepository->findPublishedForAffiliate(
            $affiliate,
            $search,
            $categoryId,
            $dateFrom,
            $dateTo,
            $cursor,
            $limit,
        );

        $items = array_map(
            static fn (Event $event) => new EventListResource($event)->toArray(),
            $found['items'],
        );
        $lastEvent = $found['items'] === [] ? null : $found['items'][array_key_last($found['items'])];

        $result = [
            'items' => $items,
            'nextCursor' => $found['hasMore'] && $lastEvent instanceof Event ? $lastEvent->getId()->toString() : null,
            'hasMore' => $found['hasMore'],
        ];

        $this->redis->setex($cacheKey, self::LIST_CACHE_TTL_SECONDS, json_encode($result, JSON_THROW_ON_ERROR));

        return $result;
    }

    private function buildListCacheKey(
        Affiliate $affiliate,
        ?string $search,
        ?UuidInterface $categoryId,
        ?DateTimeImmutable $dateFrom,
        ?DateTimeImmutable $dateTo,
        ?UuidInterface $cursor,
        int $limit,
    ): string {
        $fingerprint = md5(serialize([
            $search,
            $categoryId?->toString(),
            $dateFrom?->format('c'),
            $dateTo?->format('c'),
            $cursor?->toString(),
            $limit,
        ]));

        return sprintf('events:list:%s:%s', $affiliate->getId()->toString(), $fingerprint);
    }
}
