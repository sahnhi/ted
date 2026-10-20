<?php

namespace App\Factory\View;

use App\View\ProductView;
use App\View\TaxonView;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;

interface TaxonViewFactoryInterface
{
    /**
     * @param ProductView[] $products
     */
    public function create(TaxonInterface $taxon, array $products, string $locale): TaxonView;

    /**
     * @param ProductTaxonInterface[] $products
     */
    public function list(TaxonInterface $taxon, ChannelInterface $channel, array $products, string $locale, bool $bounds): TaxonView;
}
