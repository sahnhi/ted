<?php

declare(strict_types=1);

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\ShippingMethodCategory;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ChannelInterface;

class ShippingMethodCategoryRepository extends EntityRepository
{
    private function createEnabledForChannelQueryBuilder(ChannelInterface $channel): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('sc');

        return $queryBuilder
            ->select('sc', 'sm', 'c')
            ->innerJoin('sc.shippingMethods', 'sm')
            ->innerJoin('sm.channels', 'c')
            ->andWhere('sc.enabled = true')
            ->andWhere('sm.enabled = true')
            ->andWhere($queryBuilder->expr()->isMemberOf(':channel', 'sm.channels'))
            ->setParameter('channel', $channel);
    }

    /**
     * @return array<ShippingMethodCategory>
     */
    public function findEnabledForChannel(ChannelInterface $channel): iterable
    {
        return $this->createEnabledForChannelQueryBuilder($channel)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<ShippingMethodCategory>
     */
    public function findEnabledForZonesAndChannel(array $zones, ChannelInterface $channel): iterable
    {
        return $this->createEnabledForChannelQueryBuilder($channel)
            ->andWhere('sm.zone IN (:zones)')
            ->setParameter('zones', $zones)
            ->getQuery()
            ->getResult();
    }
}
