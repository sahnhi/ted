<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Doctrine\Entity\Sylius\OrderInterface;

final class StorageBedFrameTooLargeProcessor extends AbstractOrderMessageProcessor
{
    public const ENTRY_NAME = 'storage-bed-frame';

    public function process(OrderInterface $order): void
    {
        if (!$this->shouldProcess()) {
            return;
        }

        if ($this->hasProductVariantCodeInOrder($order, $this->data['codes'])) {
            $this->addMessage($order);
        }

        if ($this->hasProductCodeInOrder($order, $this->data['codes'])) {
            $this->addMessage($order);
        }
    }
}
