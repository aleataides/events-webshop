<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Area;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends EntityRepository<Area>
 */
final class AreaRepository extends EntityRepository
{
    /**
     * Atomic conditional UPDATE, called inside CartService::transactional —
     * see docs/shared/business-rules.md#stock-locking.
     */
    public function tryReserve(UuidInterface $areaId, int $qty): bool
    {
        $affected = $this->getEntityManager()->getConnection()->executeStatement(
            'UPDATE area SET reserved_qty = reserved_qty + :qty '
                . 'WHERE id = :id AND reserved_qty + sold_qty + :qty <= capacity',
            ['qty' => $qty, 'id' => $areaId->getBytes()],
        );

        return $affected > 0;
    }

    /**
     * Atomic conditional UPDATE finalizing a held reservation into a sale —
     * called inside CartService::transactional. Guards against a duplicate
     * concurrent Buy re-converting the same reservation twice, see
     * docs/shared/business-rules.md#buy--checkout.
     */
    public function trySell(UuidInterface $areaId, int $qty): bool
    {
        $affected = $this->getEntityManager()->getConnection()->executeStatement(
            'UPDATE area SET reserved_qty = reserved_qty - :qty, sold_qty = sold_qty + :qty '
                . 'WHERE id = :id AND reserved_qty >= :qty',
            ['qty' => $qty, 'id' => $areaId->getBytes()],
        );

        return $affected > 0;
    }
}
