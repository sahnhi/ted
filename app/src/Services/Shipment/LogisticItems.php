<?php

namespace App\Services\Shipment;

use App\Doctrine\Entity\OrderItemLogistic;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Model\ProductReferenceInterface;
use App\Services\Channel\ChannelCode;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class LogisticItems implements LogisticItemsInterface
{
    private RepositoryInterface $variantRepository;
    private RepositoryInterface $comboRepository;

    public function getLogisticItemsComboFromOrder(OrderInterface $order, RepositoryInterface $variantRepository, RepositoryInterface $comboRepository, ?OrderInterface $parentOrder = null): array
    {
        $this->variantRepository = $variantRepository;
        $this->comboRepository = $comboRepository;

        $items = new ArrayCollection();
        $parentLogisticItems = [];

        if (!empty($parentOrder)) {
            foreach ($parentOrder->getItems() as $item) {
                foreach ($item->getOrderItemLogistics() as $itemLogistic) {
                    $variantCode = $itemLogistic->getProductVariantLogistic()->getCode();
                    if (false === str_contains(
                        haystack: strtolower((string) $item->getVariant()->getCode()),
                        needle: ProductVariantInterface::BUNDLE_PREFIX)) {
                        $parentLogisticItems[$variantCode][] = $item->getVariant()->getCode();
                    }
                }
            }
        }

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            $logisticItemArray = [];

            /** @var OrderItemLogistic $itemLogistic */
            foreach ($item->getOrderItemLogistics() as $itemLogistic) {
                $itemArray = [
                    'quantity' => 1,
                    'variant' => $itemLogistic->getProductVariantLogistic(),
                    'logisticItem' => $itemLogistic,
                ];

                $logisticItemArray[] = $itemArray;
            }

            if (0 === $item->getOrderItemLogistics()->count() && (ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode() || !$this->checkQtyAlreadyHandled($item, $items))) {
                $logisticItemArray = array_merge($logisticItemArray, $this->getLogisticItemsForLineWithoutLogistic($item));
            }

            foreach ($logisticItemArray as $itemArray) {
                // TODO : to be remove from 10/2024
                if (array_key_exists('itemCode', $itemArray) && in_array($itemArray['itemCode'], ProductReferenceInterface::OLD_REPOSSESS_CODES)) {
                    continue;
                }

                $orderItem = new OrderItem();
                $orderItem->setOrder($order);
                $orderItem->setVariant($itemArray['variant']);
                $itemCombo = $this->getLogisticItemsForLineWithoutLogistic($orderItem, $itemArray['quantity']);
                if (!empty($itemCombo)) {
                    foreach ($itemCombo as $row) {
                        $orderItem = new OrderItem();
                        $orderItem->setOrder($order);
                        $orderItem->setVariant($row['variant']);

                        $this->addOrderItemLogistic($items, $orderItem, $itemArray);
                    }
                } else {
                    $this->addOrderItemLogistic($items, $orderItem, $itemArray);
                }
            }
        }

        return [$items, $parentLogisticItems];
    }

    private function getLogisticItemsForLineWithoutLogistic(OrderItem $item, int $itemQuantity = 0): array
    {
        $splited = explode('_', (string) $item->getVariant()->getCode());

        $currentItemArrays = $logisticItemArray = [];

        foreach ($splited as $code) {
            $productVariant = $this->variantRepository->findOneByCode($code);
            $productCombos = $this->comboRepository->findByProductVariant($productVariant);
            $quantity = 0 !== $item->getQuantity() ? $item->getQuantity() : $itemQuantity;

            if (!empty($productCombos)) {
                foreach ($productCombos as $productCombo) {
                    $countVariant = $quantity * $productCombo->getQuantity();
                    $variants = [];

                    if (floor($countVariant) !== $countVariant) {
                        $variants[] = [
                            'quantity' => 1,
                            'variant' => $productCombo->getProductVariant(),
                        ];
                    }

                    if ($countVariant >= 1) {
                        $variants[] = [
                            'quantity' => floor($countVariant),
                            'variant' => $productCombo->getProductVariantLogistic() ?? $productCombo->getProductVariant(),
                        ];
                    }

                    foreach ($variants as $variant) {
                        for ($i = 0; $i < $variant['quantity']; ++$i) {
                            $itemArray = [
                                'quantity' => 1,
                                'variant' => $variant['variant'],
                                'itemProduct' => $variant['variant']->getProduct(),
                            ];

                            $currentItemArrays[] = $itemArray;
                        }
                    }
                }
            } else {
                for ($i = 0; $i < $item->getQuantity(); ++$i) {
                    $itemArray = [
                        'quantity' => 1,
                        'variant' => $productVariant,
                        'itemProduct' => $item->getProduct(),
                        'total' => round($item->getTotal() / $item->getQuantity()),
                        'itemCode' => $item->getVariant()->getCode(),
                    ];
                    $logisticItemArray[] = $itemArray;
                }
            }
        }

        return array_merge($logisticItemArray, $currentItemArrays);
    }

    private function checkQtyAlreadyHandled(OrderItem $item, ArrayCollection $items): bool
    {
        $order = $item->getOrder();

        $alreadyHandledItems = $items->filter(fn (OrderItem $filteredItem): bool => $filteredItem->getVariant() === $item->getVariant());

        $alreadyHandledQuantity = $alreadyHandledItems->count();
        $quantityToHandle = 0;

        $order->getItems()->filter(function (OrderItem $orderItem) use ($item, &$quantityToHandle): bool {
            if ($item->getVariant() === $orderItem->getVariant() && $orderItem->getId()) {
                $quantityToHandle += $orderItem->getQuantity();

                return true;
            }

            return false;
        });

        return $alreadyHandledQuantity >= $quantityToHandle;
    }

    private function addOrderItemLogistic($items, $orderItem, $itemArray): void
    {
        if (isset($itemArray['logisticItem'])) {
            $orderItem->addOrderItemLogistic($itemArray['logisticItem']);
        }

        for ($i = 0; $i < $itemArray['quantity']; ++$i) {
            new OrderItemUnit($orderItem);
        }

        $items->add($orderItem);
    }
}
