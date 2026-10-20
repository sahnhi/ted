<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\ProductCombo;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ProductComboRepository extends EntityRepository implements ProductComboRepositoryInterface
{
    /**
     * @return iterable<ProductCombo>
     */
    public function findByVariantCode($variantCode)
    {
        $qb = $this->createQueryBuilder('pc')
            ->select('pc', 'v')
            ->join('pc.productVariant', 'v')
            ->where('v.code = :variantCode')
            ->setParameter('variantCode', $variantCode);

        return $qb->getQuery()->getResult();
    }
}
