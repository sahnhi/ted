<?php

declare(strict_types=1);

namespace App\Promotion\Filter;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Promotion\Filter\FilterInterface;

final class TaxonExcludeFilter implements FilterInterface
{
    public function filter(array $items, array $configuration): array
    {
        /** @var array|null $taxons */
        $taxons = $configuration['filters']['taxons_filter']['taxons'] ?? $configuration['taxons_filter']['taxons'] ?? null;

        if (null === $taxons) {
            return $items;
        }

        $filteredItems = [];
        foreach ($items as $item) {
            if ($this->notProductValidTaxon($item->getProduct(), $taxons)) {
                $filteredItems[] = $item;
            }
        }

        return $filteredItems;
    }

    private function notProductValidTaxon(ProductInterface $product, array $taxons): bool
    {
        foreach ($product->getTaxons() as $taxon) {
            if (in_array($taxon->getCode(), $taxons, true)) {
                return false;
            }
        }

        return true;
    }
}
