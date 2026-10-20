<?php

namespace App\Doctrine\Repository\Sylius;

use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductTaxonRepositoryInterface as BaseProductTaxonRepositoryInterface;

interface ProductTaxonRepositoryInterface extends BaseProductTaxonRepositoryInterface
{
    public function getFullProducts(string $locale, ?array $slugs): array;

    public function getByTaxon(TaxonInterface $taxon, array $productTaxons): array;

    public function getOneByProductId(int $id, array $productTaxons): ?ProductTaxonInterface;

    public function findAllByTaxon(TaxonInterface $taxon): array;
}
