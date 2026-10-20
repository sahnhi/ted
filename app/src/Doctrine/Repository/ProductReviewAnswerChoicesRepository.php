<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\ProductReviewAnswerChoices;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ProductReviewAnswerChoicesRepository extends EntityRepository implements ProductReviewAnswerChoicesRepositoryInterface
{
    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o');
    }

    public function getAnswerChoices(string $name, string $locale = 'fr_FR'): ProductReviewAnswerChoices
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.translations', 'translation')
            ->andWhere('translation.name = :name')
            ->andWhere('translation.locale = :locale')
            ->setParameter('name', $name, ParameterType::STRING)
            ->setParameter('locale', $locale, ParameterType::STRING);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
