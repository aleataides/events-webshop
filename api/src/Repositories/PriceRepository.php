<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Affiliate;
use App\Entities\Price;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends EntityRepository<Price>
 */
final class PriceRepository extends EntityRepository
{
    public function findOneForAffiliate(Affiliate $affiliate, UuidInterface $priceId): ?Price
    {
        return $this->createQueryBuilder('p')
            ->addSelect('area')
            ->join('p.area', 'area')
            ->join('area.event', 'event')
            ->andWhere('p.id = :id')
            ->andWhere('IDENTITY(event.affiliate) = :affiliateId')
            ->setParameter('id', $priceId, UuidBinaryType::NAME)
            ->setParameter('affiliateId', $affiliate->getId(), UuidBinaryType::NAME)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
