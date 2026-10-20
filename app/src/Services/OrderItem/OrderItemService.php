<?php

namespace App\Services\OrderItem;

use App\Doctrine\Entity\BundleAssociationInterface;
use App\Doctrine\Entity\OrderItemLogistic;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\ProductComboRepository;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Webmozart\Assert\Assert;

class OrderItemService
{
    public function __construct(
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly ProductComboRepository $productComboRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function generateItemLogistic($order): void
    {
        $logisticItemArray = $itemCodes = [];
        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            /** @var ProductVariantInterface $variant */
            $variant = $item->getVariant();

            Assert::notNull($variant, sprintf('Item variant cannot be null'));

            if ($variant->isBundle()) {
                /** @var BundleAssociationInterface $bundleAssociation */
                foreach ($variant->getBundleAssociations() as $bundleAssociation) {
                    $this->handleLogisticItemArray(
                        $bundleAssociation->getVariant(),
                        $logisticItemArray,
                        $item->getQuantity() * $bundleAssociation->getQuantity(),
                        $bundleAssociation->getQuantity() * $bundleAssociation->getPrice()
                    );
                    array_pop($logisticItemArray);
                }
            } else {
                $this->handleLogisticItemArray(
                    $item->getVariant(),
                    $logisticItemArray,
                    $item->getQuantity(),
                    $item->getTotal()
                );
            }
        }

        foreach ($logisticItemArray as $row) {
            if (isset($row['itemCode'], $row['variant'], $row['quantity'])) {
                $itemCodes[$row['itemCode']][] = [
                    'code' => $row['variant']->getCode(),
                    'quantity' => $row['quantity'],
                ];
                unset($row['itemCode']);
            }
        }

        if (0 === count($order->getOrderItemLogistics())) {
            foreach ($order->getItems() as $item) {
                $this->checkOrderItemLogistics($item, $itemCodes);
            }
        }

        if (!empty($order->getId())) {
            $this->entityManager->flush();
        }
    }

    private function checkOrderItemLogistics(OrderItemInterface $item, array &$itemCodes): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $item->getVariant();

        if ($variant->isBundle()) {
            /** @var BundleAssociationInterface $association */
            foreach ($variant->getBundleAssociations() as $association) {
                $this->handleBundleOrderItemLogistic($item, $association);
            }
        } elseif (isset($itemCodes[$item->getVariant()->getCode()])) {
            $this->handleOrderItemLogistic($item, $item->getVariant(), $itemCodes);
        }
    }

    private function handleOrderItemLogistic(
        OrderItemInterface $item,
        ProductVariantInterface $productVariant,
        array &$itemCodes
    ): void {
        $variantLogistic = null;

        foreach ($itemCodes[$productVariant->getCode()] as $itemVariant) {
            for ($i = 0; $i < $itemVariant['quantity']; ++$i) {
                if (0 === $i) {
                    $variantLogistic = $this->productVariantRepository->findOneBy(['code' => $itemVariant['code']]);
                }
                if (null !== $variantLogistic) {
                    $orderItemLogistic = new OrderItemLogistic();
                    $orderItemLogistic->setOrderItem($item);
                    $orderItemLogistic->setProductVariantLogistic($variantLogistic);
                    $this->entityManager->persist($orderItemLogistic);
                }
            }
        }

        unset($itemCodes[$productVariant->getCode()]);
    }

    private function handleBundleOrderItemLogistic(
        OrderItemInterface $item,
        BundleAssociationInterface $bundleAssociation
    ): void {
        $totalQuantity = $item->getQuantity() * $bundleAssociation->getQuantity();
        for ($i = 0; $i < $totalQuantity; ++$i) {
            $orderItemLogistic = new OrderItemLogistic();
            $orderItemLogistic->setOrderItem($item);
            $orderItemLogistic->setProductVariantLogistic($bundleAssociation->getVariant());
            $item->addOrderItemLogistic($orderItemLogistic);
            $this->entityManager->persist($item);
        }
    }

    public function handleLogisticItemArray(
        ProductVariantInterface $mainVariant,
        array &$logisticItemArray,
        int $quantity,
        int $total
    ): void {
        $currentItemArrays = [];
        $totalPackages = 0;

        $splited = explode('_', (string) $mainVariant->getCode());

        foreach ($splited as $code) {
            $productVariant = $this->productVariantRepository->findOneByCode($code);
            $productCombos = $this->productComboRepository->findByProductVariant($productVariant);

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
                        $itemArray = [
                            'quantity' => $variant['quantity'],
                            'variant' => $variant['variant'],
                            'itemProduct' => $variant['variant']->getProduct(),
                        ];

                        $totalPackages += $variant['quantity'];
                        $currentItemArrays[] = $itemArray;
                    }
                }
            } else {
                $totalPackages += $quantity;

                $itemArray = [
                    'quantity' => $quantity,
                    'variant' => $productVariant,
                    'itemProduct' => $mainVariant->getProduct(),
                ];

                $currentItemArrays[] = $itemArray;
            }
        }

        foreach ($currentItemArrays as $key => $itemArray) {
            $currentItemArrays[$key]['total'] = round($total / $totalPackages) * $itemArray['quantity'];
            $currentItemArrays[$key]['itemCode'] = $mainVariant->getCode();
            $currentItemArrays[$key]['product'] = $mainVariant->getProduct();
        }

        $logisticItemArray = array_merge($logisticItemArray, $currentItemArrays);
    }
}
