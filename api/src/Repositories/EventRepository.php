<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Affiliate;
use App\Entities\Event;
use DateTimeImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends EntityRepository<Event>
 */
final class EventRepository extends EntityRepository
{
    /**
     * @param list<UuidInterface> $categoryIds
     * @return array{items: list<Event>, hasMore: bool}
     */
    public function findPublishedForAffiliate(
        Affiliate $affiliate,
        ?string $search,
        array $categoryIds,
        ?DateTimeImmutable $dateFrom,
        ?DateTimeImmutable $dateTo,
        ?UuidInterface $cursor,
        int $limit,
    ): array {
        // LIMIT can't apply to a query joined to to-many associations (rows
        // multiply) — page over ids first, then fetch the full graph.
        $idQuery = $this->createQueryBuilder('e')
            ->andWhere('IDENTITY(e.affiliate) = :affiliateId')
            ->andWhere('e.status = :status')
            ->setParameter('affiliateId', $affiliate->getId(), UuidBinaryType::NAME)
            ->setParameter('status', 'PUBLISHED')
            ->orderBy('e.id', 'ASC')
            ->setMaxResults($limit + 1);

        if ($search !== null && $search !== '') {
            $idQuery->andWhere('e.title LIKE :search')->setParameter('search', '%' . $search . '%');
        }
        if ($categoryIds !== []) {
            // distinct(): an event matching 2+ of the selected categories
            // would otherwise fan out into duplicate rows via the join.
            $categoryIdBytes = array_map(static fn (UuidInterface $id) => $id->getBytes(), $categoryIds);
            $idQuery->distinct()
                ->join('e.categories', 'c')
                ->andWhere('c.id IN (:categoryIds)')
                ->setParameter('categoryIds', $categoryIdBytes, ArrayParameterType::BINARY);
        }
        if ($dateFrom instanceof DateTimeImmutable) {
            $idQuery->andWhere('e.start >= :dateFrom')->setParameter('dateFrom', $dateFrom);
        }
        if ($dateTo instanceof DateTimeImmutable) {
            $idQuery->andWhere('e.start <= :dateTo')->setParameter('dateTo', $dateTo);
        }
        if ($cursor instanceof UuidInterface) {
            $idQuery->andWhere('e.id > :cursor')->setParameter('cursor', $cursor, UuidBinaryType::NAME);
        }

        /** @var list<Event> $pageEvents */
        $pageEvents = $idQuery->getQuery()->getResult();

        $hasMore = count($pageEvents) > $limit;
        $idBytes = array_map(
            static fn (Event $event) => $event->getId()->getBytes(),
            array_slice($pageEvents, 0, $limit),
        );

        if ($idBytes === []) {
            return ['items' => [], 'hasMore' => false];
        }

        // Raw bytes + ArrayParameterType::BINARY — a custom Doctrine type
        // isn't applied per-element when expanding an IN (:ids) parameter.
        /** @var list<Event> $events */
        $events = $this->createQueryBuilder('e')
            ->addSelect('venue', 'areas', 'prices', 'categories')
            ->join('e.venue', 'venue')
            ->leftJoin('e.areas', 'areas')
            ->leftJoin('areas.prices', 'prices')
            ->leftJoin('e.categories', 'categories')
            ->andWhere('e.id IN (:ids)')
            ->setParameter('ids', $idBytes, ArrayParameterType::BINARY)
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();

        return ['items' => $events, 'hasMore' => $hasMore];
    }

    public function findOneForAffiliate(Affiliate $affiliate, UuidInterface $eventId): ?Event
    {
        return $this->createQueryBuilder('e')
            ->addSelect('venue', 'areas', 'prices', 'categories')
            ->join('e.venue', 'venue')
            ->leftJoin('e.areas', 'areas')
            ->leftJoin('areas.prices', 'prices')
            ->leftJoin('e.categories', 'categories')
            ->andWhere('e.id = :id')
            ->andWhere('IDENTITY(e.affiliate) = :affiliateId')
            ->andWhere('e.status = :status')
            ->setParameter('id', $eventId, UuidBinaryType::NAME)
            ->setParameter('affiliateId', $affiliate->getId(), UuidBinaryType::NAME)
            ->setParameter('status', 'PUBLISHED')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
