<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Affiliate;
use App\Entities\Category;
use App\Entities\Event;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidBinaryType;

/**
 * @extends EntityRepository<Category>
 */
final class CategoryRepository extends EntityRepository
{
    /**
     * @return list<Category>
     */
    public function findPublishedForAffiliate(Affiliate $affiliate): array
    {
        // DQL can't SELECT a joined (non-root) entity, so root on Category
        // and use EXISTS — there's no inverse Category->Event association.
        $exists = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(Event::class, 'e')
            ->join('e.categories', 'ec')
            ->where('ec = c')
            ->andWhere('IDENTITY(e.affiliate) = :affiliateId')
            ->andWhere('e.status = :status');

        /** @var list<Category> $categories */
        $categories = $this->createQueryBuilder('c')
            ->andWhere($this->createQueryBuilder('c')->expr()->exists($exists->getDQL()))
            ->setParameter('affiliateId', $affiliate->getId(), UuidBinaryType::NAME)
            ->setParameter('status', 'PUBLISHED')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $categories;
    }
}
