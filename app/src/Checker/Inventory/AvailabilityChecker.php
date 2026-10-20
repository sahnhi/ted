<?php

declare(strict_types=1);

namespace App\Checker\Inventory;

use App\Doctrine\Entity\Sylius\ProductVariant;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Inventory\Model\StockableInterface;

/**
 * This AvailabilityChecker considers that a variant can always be ordered as long as it is enabled (which at this point is always the case).
 * The stock is handled manually : a variant is disabled when the ERP says there's no stock, and enabled during after restocking.
 */
class AvailabilityChecker implements AvailabilityCheckerInterface
{
    /**
     * @param ProductVariant $stockable
     */
    public function isStockAvailable(StockableInterface $stockable): bool
    {
        return true;
    }

    public function isStockSufficient(StockableInterface $stockable, int $quantity): bool
    {
        return true;
    }
}
