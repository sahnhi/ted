<?php

namespace App\Doctrine\Repository\Sylius;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderItemRepository as BaseOrderItemRepository;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Repository\OrderItemRepositoryInterface as BaseOrderItemRepositoryInterface;
use SyliusLabs\AssociationHydrator\AssociationHydrator;

class OrderItemRepository extends BaseOrderItemRepository implements BaseOrderItemRepositoryInterface
{
    /** @var AssociationHydrator */
    protected $associationHydrator;

    public function __construct(EntityManagerInterface $entityManager, Mapping\ClassMetadata $class)
    {
        parent::__construct($entityManager, $class);

        $this->associationHydrator = new AssociationHydrator($entityManager, $class);
    }

    public function getOneByIdHydrated(int $id): ?OrderItemInterface
    {
        $orderItem = $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        $this->associationHydrator->hydrateAssociations($orderItem, [
            'adjustments',
            'units',
        ]);

        return $orderItem;
    }
}
