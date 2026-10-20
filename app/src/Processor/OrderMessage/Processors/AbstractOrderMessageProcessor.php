<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Model\Order\OrderMessage;
use App\Processor\OrderMessage\Processors\Interfaces\OrderMessageProcessorItemInterface;
use App\Processor\OrderMessage\Processors\Interfaces\ProductCodeInOrderStartsWithInterface;
use App\Processor\OrderMessage\Processors\Interfaces\ProductVariantCodeInOrderInterface;
use App\Processor\OrderMessage\Processors\Interfaces\ProductVariantCodeInOrderStartsWithInterface;

abstract class AbstractOrderMessageProcessor implements OrderMessageProcessorItemInterface
{
    protected ?array $data = null;

    public const ENTRY_NAME = '';

    public function __construct(
        array $data,
    ) {
        if (isset($data[static::ENTRY_NAME])) {
            $this->data = $data[static::ENTRY_NAME];
        }
    }

    public function process(OrderInterface $order): void
    {
        if (!$this->shouldProcess()) {
            return;
        }

        $hasMatch = match (true) {
            $this instanceof ProductVariantCodeInOrderInterface => $this->hasProductVariantCodeInOrder($order, $this->data['codes']),
            $this instanceof ProductVariantCodeInOrderStartsWithInterface => $this->hasProductVariantCodeInOrderStartsWith($order, $this->data['codes']),
            $this instanceof ProductCodeInOrderStartsWithInterface => $this->hasProductCodeInOrderStartsWith($order, $this->data['codes']),
            default => $this->hasProductCodeInOrder($order, $this->data['codes']),
        };

        if ($hasMatch) {
            $this->addMessage($order);
        }
    }

    protected function shouldProcess(): bool
    {
        // this case is added in case the message is not activated on configuration file
        if (is_null($this->data)) {
            return false;
        }

        return true;
    }

    protected function hasProductCodeInOrder(OrderInterface $order, array $productCode): bool
    {
        foreach ($order->getItems() as $item) {
            if (in_array($item->getProduct()->getCode(), $productCode)) {
                return true;
            }
        }

        return false;
    }

    protected function hasProductVariantCodeInOrder(Order $order, array $productVariantCode): bool
    {
        foreach ($order->getItems() as $item) {
            if (in_array($item->getVariant()->getCode(), $productVariantCode)) {
                return true;
            }
        }

        return false;
    }

    protected function hasProductCodeInOrderStartsWith(Order $order, array $productCode): bool
    {
        foreach ($order->getItems() as $item) {
            foreach ($productCode as $code) {
                if (str_starts_with(haystack: (string) $item->getProduct()->getCode(), needle: (string) $code)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasProductVariantCodeInOrderStartsWith(Order $order, array $productVariantCode): bool
    {
        foreach ($order->getItems() as $item) {
            foreach ($productVariantCode as $code) {
                if (str_starts_with(haystack: (string) $item->getVariant()->getCode(), needle: (string) $code)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function addMessage(Order $order, ?string $text = null, ?string $type = null): void
    {
        $text ??= $this->data['messages']['message'];
        $type ??= $this->data['messages']['type'];

        $message = new OrderMessage($text, $type);
        $order->addMessage($message);
    }
}
