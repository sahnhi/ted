<?php

declare(strict_types=1);

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\ProductInterface;
use Doctrine\DBAL\Types\Types;
use Setono\SyliusCatalogPromotionPlugin\Doctrine\ORM\ChannelPricingRepositoryTrait as CatalogPromotionChannelPricingRepositoryTrait;
use Setono\SyliusCatalogPromotionPlugin\Repository\ChannelPricingRepositoryInterface as CatalogPromotionChannelPricingRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;

class ChannelPricingRepository extends EntityRepository implements CatalogPromotionChannelPricingRepositoryInterface
{
    use CatalogPromotionChannelPricingRepositoryTrait;

    public function getLowestPriceForProduct(ProductInterface $product, ChannelInterface $channel): ?ChannelPricingInterface
    {
        return $this->createQueryBuilder('cp')
            ->andWhere('cp.channelCode = :channel')
            ->innerJoin('cp.productVariant', 'pv')
            ->andWhere('pv.product = :product')
            ->andWhere('pv.enabled = :enabled')
            ->setParameter('product', $product)
            ->setParameter('channel', $channel->getCode(), Types::STRING)
            ->setParameter('enabled', true, Types::BOOLEAN)
            ->orderBy('cp.price', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
