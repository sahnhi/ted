<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Sylius\ProductInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ProductGoogleRssRepository extends EntityRepository implements RepositoryInterface, ProductGoogleRssRepositoryInterface
{
    public function getForProductVariants(ProductInterface $product): array
    {
        return $this->createQueryBuilder('pgr')
            ->innerJoin('pgr.variant', 'v')
            ->where('v.product = :product')
            ->andWhere('pgr.sku is not null')
            ->andWhere('pgr.gtin is not null')
            ->andWhere('pgr.mpn is not null')
            ->andWhere('pgr.productUrl is not null')
            ->setParameter('product', $product)
            ->getQuery()->getResult();
    }
}
