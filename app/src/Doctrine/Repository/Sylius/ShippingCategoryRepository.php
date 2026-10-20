<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\ShippingCategory;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShippingCategoryRepository as BaseShippingCategoryRepository;

class ShippingCategoryRepository extends BaseShippingCategoryRepository
{
    /**
     * @throws NonUniqueResultException
     */
    public function findShippingCategoryByVariant($variant): ShippingCategory|null
    {
        return $this->createQueryBuilder('c')
            ->join('Sylius:ProductVariant', 'variant')
            ->andWhere('c.id = variant.shippingCategory')
            ->andWhere('variant.code = :variant')
            ->setParameter('variant', $variant)
            ->getQuery()->getOneOrNullResult();
    }
}
