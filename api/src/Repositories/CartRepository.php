<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Affiliate;
use App\Entities\Cart;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends EntityRepository<Cart>
 */
final class CartRepository extends EntityRepository
{
    public function findOneForAffiliate(Affiliate $affiliate, UuidInterface $cartId): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->addSelect('reservations', 'price')
            ->leftJoin('c.reservations', 'reservations')
            ->leftJoin('reservations.price', 'price')
            ->andWhere('c.id = :id')
            ->andWhere('IDENTITY(c.affiliate) = :affiliateId')
            ->setParameter('id', $cartId, UuidBinaryType::NAME)
            ->setParameter('affiliateId', $affiliate->getId(), UuidBinaryType::NAME)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
