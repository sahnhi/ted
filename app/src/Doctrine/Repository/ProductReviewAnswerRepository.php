<?php

namespace App\Doctrine\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ProductReviewAnswerRepository extends EntityRepository implements RepositoryInterface
{
    public function getReviewWithMultiAnswer()
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.answer LIKE :answer')
            ->setParameter('answer', '%,%');

        return $qb->getQuery()->getResult();
    }
}
