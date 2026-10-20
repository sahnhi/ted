<?php

declare(strict_types=1);

namespace App\Promotion\Filter;

use Sylius\Component\Core\Promotion\Filter\FilterInterface;

final class ProductExcludeFilter implements FilterInterface
{
    public function filter(array $items, array $configuration): array
    {
        /** @var array|null $products */
        $products = $configuration['filters']['products_filter']['products'] ?? $configuration['products_filter']['products'] ?? null;

        if (null === $products) {
            return $items;
        }

        $filteredItems = [];
        foreach ($items as $item) {
            if (!in_array($item->getProduct()->getCode(), $products, true)) {
                $filteredItems[] = $item;
            }
        }

        return $filteredItems;
    }
}
