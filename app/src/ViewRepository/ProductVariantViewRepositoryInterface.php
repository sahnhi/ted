<?php

namespace App\ViewRepository;

use App\View\ProductVariantListView;
use Doctrine\ORM\NonUniqueResultException;

interface ProductVariantViewRepositoryInterface
{
    /**
     * @throws NonUniqueResultException
     */
    public function findAllPerOptions(string $channelCode, string $productCode, array $options): ProductVariantListView;
}
