<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Doctrine\Entity\Sylius\OrderInterface;

final class BedHeadAndWoodBedFrameProcessor extends AbstractOrderMessageProcessor
{
    public const ENTRY_NAME = 'bed_frame_and_wood_bed_frame';

    public function process(OrderInterface $order): void
    {
        if (!$this->shouldProcess()) {
            return;
        }

        $hasWoodBedFrame = false;
        if (isset($this->data['codes']['wood_bed_frame_code'])) {
            $hasWoodBedFrame = $this->hasProductCodeInOrder($order, $this->data['codes']['wood_bed_frame_code']);
        }

        $hasRegularBedHead = false;
        if (isset($this->data['codes']['regular_bed_head_code'])) {
            $hasRegularBedHead = $this->hasProductCodeInOrder($order, $this->data['codes']['regular_bed_head_code']);
        }

        $hasWoodBedHead = false;
        if (isset($this->data['codes']['wood_bed_head_code'])) {
            $hasWoodBedHead = $this->hasProductCodeInOrder($order, $this->data['codes']['wood_bed_head_code']);
        }

        if ($hasWoodBedFrame && $hasRegularBedHead && !$hasWoodBedHead) {
            $this->addMessage($order);
        }
    }
}
