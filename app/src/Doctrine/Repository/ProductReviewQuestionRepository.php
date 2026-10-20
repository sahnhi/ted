<?php

namespace App\Doctrine\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ProductReviewQuestionRepository extends EntityRepository implements ProductReviewQuestionRepositoryInterface
{
    public function findQuestionsByProductId(string $productId): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->select('o')
            ->leftJoin('o.product', 'p')
            ->andWhere('p.id = :id')
            ->setParameter('id', (int) $productId);
    }
}
