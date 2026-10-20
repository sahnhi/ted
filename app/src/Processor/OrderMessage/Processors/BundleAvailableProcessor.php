<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Doctrine\Entity\Sylius\OrderInterface;

final class BundleAvailableProcessor extends AbstractOrderMessageProcessor
{
    public const ENTRY_NAME = 'bundle_available';

    public function process(OrderInterface $order): void
    {
        if (!$this->shouldProcess()) {
            return;
        }

        $hasMattress = false;
        if (isset($this->data['codes']['mattress_code'])) {
            $hasMattress = $this->hasProductCodeInOrder($order, $this->data['codes']['mattress_code']);
        }

        $hasBedBox = false;
        if (isset($this->data['codes']['bed_box_code'])) {
            $hasBedBox = $this->hasProductCodeInOrder($order, $this->data['codes']['bed_box_code']);
        }

        if ($hasMattress && $hasBedBox) {
            $this->addMessage($order);
        }
    }
}
