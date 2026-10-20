<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Doctrine\Entity\Sylius\OrderInterface;

final class VoluminousProductOnCartProcessor extends AbstractOrderMessageProcessor
{
    public const ENTRY_NAME = 'voluminous_product_on_cart';

    public function process(OrderInterface $order): void
    {
        if (!$this->shouldProcess()) {
            return;
        }

        if ($this->hasProductCodeInOrder($order, $this->data['codes']['product_codes'])
            || $this->hasProductVariantCodeInOrder($order, $this->data['codes']['product_variant_codes'])
        ) {
            $this->addMessage($order);
        }
    }
}
