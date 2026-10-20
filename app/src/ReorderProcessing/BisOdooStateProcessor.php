<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use App\Api\Odoo\Model\OrderInterface;
use App\Doctrine\Entity\Sylius\OrderInterface as EntityOrderInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use Webmozart\Assert\Assert;

final class BisOdooStateProcessor implements ReorderProcessor
{
    public function process(BaseOrderInterface $order, BaseOrderInterface $reorder): void
    {
        Assert::isInstanceOf($reorder, EntityOrderInterface::class);
        /* @var EntityOrderInterface $reorder */
        $reorder->setOdooState(OrderInterface::ODOO_STATE_CREATED);
    }
}
