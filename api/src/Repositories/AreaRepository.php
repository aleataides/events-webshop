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
     * Atomic conditional UPDATE, no row locks — see
     * docs/shared/business-rules.md#stock-locking.
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
}
