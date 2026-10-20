<?php

namespace App\Handler;

use App\Business\Command\RemoveItemFromCart;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Handler\Shared\OrderResetter;
use App\Model\ProductReferenceInterface;
use SM\SMException;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Order\Repository\OrderItemRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Webmozart\Assert\Assert;

final class RemoveItemFromCartHandler
{
    public function __construct(
        private readonly OrderItemRepositoryInterface $orderItemRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly OrderResetter $orderResetter,
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(RemoveItemFromCart $command): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->getOneByTokenValueHydrated($command->orderToken());
        Assert::notNull($order, sprintf('Cart with %s token has not been found.', $command->orderToken()));

        if ($order->isLocked()) {
            throw new ConflictHttpException('Cet article ne peut pas être retiré de votre panier. Merci de faire une nouvelle commande.');
        }

        $this->orderResetter->cancelPaymentAndResetOrder($order);

        /** @var OrderItemInterface $orderItem */
        $orderItem = $this->orderItemRepository->getOneByIdHydrated($command->itemIdentifier());
        Assert::notNull($orderItem, 'Cart item has not been found.');
        Assert::true($order->hasItem($orderItem), sprintf('Cart item with %s id does not exists', $command->itemIdentifier()));

        $order->removeItem($orderItem);

        // to remove returning products as well
        $productCode = $orderItem->getVariant()->getProduct()->getCode();
        if (in_array($productCode, ProductReferenceInterface::REF_MATTRESSES)) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::REP_MAT === $item->getProduct()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        if (in_array($productCode, ProductReferenceInterface::REF_REP_BED_FRAME)) {
            foreach ($order->getItems() as $item) {
                if (!empty(ProductReferenceInterface::REF_REP_BED_FRAME[$item->getProduct()->getCode()])
                    && $productCode === ProductReferenceInterface::REF_REP_BED_FRAME[$item->getProduct()->getCode()]
                ) {
                    $order->removeItem($item);
                }
            }
        }

        if (ProductInterface::PRODUCT_BED_HEAD === $productCode) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::REP_TET === $item->getProduct()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        if (in_array($productCode, ProductReferenceInterface::SOFA)) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::MONT_CP === $item->getVariant()->getCode() || ProductReferenceInterface::REP_SOF === $item->getProduct()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        if (ProductReferenceInterface::SOFA_SIMPLE_PRODUCT_CODE === $productCode) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::REP_CANAP === $item->getVariant()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        if (in_array($productCode, ProductReferenceInterface::PELOTE)) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::INST_PEL === $item->getVariant()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        if (in_array($productCode, ProductReferenceInterface::STORAGE_BED)) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::MONT_SC === $item->getVariant()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        if (in_array($productCode, ProductReferenceInterface::ELECTRIC_BED_BEDFRAME)) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::MONT_TPR === $item->getVariant()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        if (in_array($productCode, ProductReferenceInterface::WOOD_BEDFRAME)) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::MONT_SBOIS === $item->getVariant()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        if (in_array($productCode, ProductReferenceInterface::GO)) {
            foreach ($order->getItems() as $item) {
                if (ProductReferenceInterface::MONT_GO === $item->getVariant()->getCode()) {
                    $order->removeItem($item);
                }
            }
        }

        $this->orderProcessor->process($order);
    }
}
