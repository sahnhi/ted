<?php

namespace App\Component\Order\Processor;

use Laminas\Stdlib\PriorityQueue;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;

class CompositeReorderProcessor implements ReorderProcessor
{
    private array $preProcessors;

    private array $postProcessors;

    public function __construct()
    {
        $this->postProcessors = $this->preProcessors = [];
    }

    public function getPreProcessors(): array
    {
        return $this->preProcessors;
    }

    public function getPostProcessors(): array
    {
        return $this->postProcessors;
    }

    /**
     * @param false $pre | Pre Event = true / Post Event = false
     */
    public function process(OrderInterface $order, OrderInterface $reorder, ?string $orderType = null, bool $pre = false): void
    {
        $processors = $pre ? $this->preProcessors : $this->postProcessors;

        if (isset($processors[$orderType])) {
            foreach ($processors[$orderType] as $reorderProcessor) {
                $reorderProcessor->process($order, $reorder);
            }
        }
    }

    /**
     * @param false $pre | Pre Event = true / Post Event = false
     */
    public function addProcessor(ReorderProcessor $orderProcessor, int $priority = 0, ?string $orderTypes = null, bool $pre = false): void
    {
        $arrayTypes = explode('_', (string) $orderTypes);

        foreach ($arrayTypes as $orderType) {
            if ($pre) {
                if (!isset($this->preProcessors[$orderType])) {
                    $this->preProcessors[$orderType] = new PriorityQueue();
                }

                $this->preProcessors[$orderType]->insert($orderProcessor, $priority);
            } else {
                if (!isset($this->postProcessors[$orderType])) {
                    $this->postProcessors[$orderType] = new PriorityQueue();
                }

                $this->postProcessors[$orderType]->insert($orderProcessor, $priority);
            }
        }
    }
}
