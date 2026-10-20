<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\ProductCombo;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface ProductComboRepositoryInterface extends RepositoryInterface
{
    /**
     * @return iterable<ProductCombo>
     */
    public function findByVariantCode($variantCode);
}
