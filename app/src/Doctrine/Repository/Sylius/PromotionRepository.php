<?php

declare(strict_types=1);

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\Promotion;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PromotionRepository as BasePromotionRepository;
use Sylius\Component\Core\Model\Channel;
use SyliusLabs\AssociationHydrator\AssociationHydrator;

class PromotionRepository extends BasePromotionRepository implements PromotionRepositoryInterface
{
    private readonly AssociationHydrator $associationHydrator;

    public function __construct(EntityManager $entityManager, ClassMetadata $class)
    {
        parent::__construct($entityManager, $class);

        $this->associationHydrator = new AssociationHydrator($entityManager, $class);
    }

    protected function filterByActive(QueryBuilder $queryBuilder, ?\DateTimeInterface $date = null, bool $isAppliedAutomatically = true): QueryBuilder
    {
        $queryBuilder = parent::filterByActive($queryBuilder, $date);
        $queryBuilder
            ->andWhere('o.automaticallyApplied = :isAppliedAutomatically')
            ->setParameter('isAppliedAutomatically', $isAppliedAutomatically);

        return $queryBuilder;
    }

    /**
     * @return iterable<Promotion>
     */
    public function findActiveByCodeAndChannel(array $codes, Channel $channel, bool $isAppliedAutomatically = true): iterable
    {
        /** @var iterable<Promotion> $promotions */
        $promotions = $this->filterByActive($this->createQueryBuilder('o'), isAppliedAutomatically: $isAppliedAutomatically)
            ->andWhere(':channel MEMBER OF o.channels')
            ->andWhere('o.code IN (:codes)')
            ->setParameter('channel', $channel)
            ->setParameter('codes', $codes)
            ->addOrderBy('o.priority', 'DESC')
            ->getQuery()
            ->getResult();

        $this->associationHydrator->hydrateAssociations($promotions, [
            'rules',
        ]);

        return $promotions;
    }
}
