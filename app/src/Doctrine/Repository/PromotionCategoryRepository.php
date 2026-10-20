<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Store;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @method Store|null find($id, $lockMode = null, $lockVersion = null)
 * @method Store|null findOneBy(array $criteria, array $orderBy = null)
 * @method Store|null findOneById(int $id)
 * @method Store|null findOneByName(string $name)
 * @method Store[]    findAll()
 * @method Store[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionCategoryRepository extends EntityRepository implements RepositoryInterface
{
    public function findEnabledPromotionCategory(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();
    }
}
