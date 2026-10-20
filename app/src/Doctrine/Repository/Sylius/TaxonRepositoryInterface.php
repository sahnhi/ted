<?php

namespace App\Doctrine\Repository\Sylius;

use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface as BaseTaxonRepositoryInterface;

interface TaxonRepositoryInterface extends BaseTaxonRepositoryInterface
{
    /**
     * @return Taxon[]
     */
    public function findAllByLocale(string $locale, ?array $slugs = null): array;

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByLocaleAndSlug(string $locale, string $slug): ?Taxon;

    public function findAllEnabledByLocale(string $locale): ?array;
}
