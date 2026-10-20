<?php

namespace App\ViewRepository;

use App\View\ProductView;
use App\View\TaxonListView;
use App\View\TaxonView;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Core\Model\TaxonInterface;

interface TaxonViewRepositoryInterface
{
    public function findAllByChannelAndLocale(string $channelCode, string $localeCode, ?array $slugs = null, bool $bounds = false): TaxonListView;

    /**
     * @throws NonUniqueResultException
     */
    public function findOneBySlug(string $channelCode, string $slug, string $localeCode): TaxonView;

    /**
     * @return ProductView[]
     */
    public function findProductsByTaxon(string $channelCode, TaxonInterface $taxon, string $localeCode): array;
}
