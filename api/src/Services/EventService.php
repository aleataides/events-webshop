<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Affiliate;
use App\Entities\Event;
use App\Exceptions\EventNotFoundException;
use App\Repositories\EventRepository;
use App\Resources\EventDetailResource;
use App\Resources\EventListResource;
use DateTimeImmutable;
use Predis\ClientInterface;
use Ramsey\Uuid\UuidInterface;

final class EventService
{
    private const int LIST_CACHE_TTL_SECONDS = 60;

    private const int DETAIL_CACHE_TTL_SECONDS = 60;

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ClientInterface $redis,
    ) {
    }

    /**
     * @param list<UuidInterface> $categoryIds
     * @return array{items: list<array<string, mixed>>, nextCursor: ?string, hasMore: bool}
     */
    public function listPublished(
        Affiliate $affiliate,
        ?string $search,
        array $categoryIds,
        ?DateTimeImmutable $dateFrom,
        ?DateTimeImmutable $dateTo,
        ?UuidInterface $cursor,
        int $limit,
    ): array {
        $cacheKey = $this->buildListCacheKey($affiliate, $search, $categoryIds, $dateFrom, $dateTo, $cursor, $limit);

        $cached = $this->redis->get($cacheKey);
        if (is_string($cached)) {
            /** @var array{items: list<array<string, mixed>>, nextCursor: ?string, hasMore: bool} $decoded */
            $decoded = json_decode($cached, true, flags: JSON_THROW_ON_ERROR);

            return $decoded;
        }

        $found = $this->eventRepository->findPublishedForAffiliate(
            $affiliate,
            $search,
            $categoryIds,
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

    /**
     * @return array<string, mixed>
     */
    public function getDetail(Affiliate $affiliate, UuidInterface $eventId): array
    {
        $cacheKey = sprintf('events:detail:%s:%s', $affiliate->getId()->toString(), $eventId->toString());

        $cached = $this->redis->get($cacheKey);
        if (is_string($cached)) {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($cached, true, flags: JSON_THROW_ON_ERROR);

            return $decoded;
        }

        $event = $this->eventRepository->findOneForAffiliate($affiliate, $eventId);
        if (!$event instanceof Event) {
            throw new EventNotFoundException($eventId->toString());
        }

        $result = new EventDetailResource($event)->toArray();

        $this->redis->setex($cacheKey, self::DETAIL_CACHE_TTL_SECONDS, json_encode($result, JSON_THROW_ON_ERROR));

        return $result;
    }

    /**
     * @param list<UuidInterface> $categoryIds
     */
    private function buildListCacheKey(
        Affiliate $affiliate,
        ?string $search,
        array $categoryIds,
        ?DateTimeImmutable $dateFrom,
        ?DateTimeImmutable $dateTo,
        ?UuidInterface $cursor,
        int $limit,
    ): string {
        $sortedCategoryIds = array_map(static fn (UuidInterface $id) => $id->toString(), $categoryIds);
        sort($sortedCategoryIds);

        $fingerprint = md5(serialize([
            $search,
            $sortedCategoryIds,
            $dateFrom?->format('c'),
            $dateTo?->format('c'),
            $cursor?->toString(),
            $limit,
        ]));

        return sprintf('events:list:%s:%s', $affiliate->getId()->toString(), $fingerprint);
    }
}
