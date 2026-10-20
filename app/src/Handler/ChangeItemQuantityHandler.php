<?php

namespace App\Handler;

use App\Business\Command\ChangeItemQuantity;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Handler\Shared\OrderResetter;
use SM\SMException;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Order\Repository\OrderItemRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Webmozart\Assert\Assert;

final class ChangeItemQuantityHandler implements ChangeItemQuantityHandlerInterface
{
    public function __construct(
        private readonly OrderItemRepositoryInterface $orderItemRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderItemQuantityModifierInterface $orderItemModifier,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly OrderResetter $orderResetter,
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(ChangeItemQuantity $changeItemQuantity): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->getOneByTokenValueHydrated($changeItemQuantity->orderToken());
        Assert::notNull($order, sprintf('Cart with %s token has not been found.', $changeItemQuantity->orderToken()));

        if ($order->isLocked()) {
            throw new ConflictHttpException('La quantité de cet article ne peut pas être modifié à votre panier. Merci de faire une nouvelle commande.');
        }

        $this->orderResetter->cancelPaymentAndResetOrder($order);

        /** @var OrderItemInterface $orderItem */
        $orderItem = $this->orderItemRepository->getOneByIdHydrated($changeItemQuantity->itemIdentifier());
        Assert::notNull($orderItem, 'Cart item has not been found.');
        Assert::true($order->hasItem($orderItem), sprintf('Cart item with %s id does not exists', $changeItemQuantity->itemIdentifier()));

        $this->orderItemModifier->modify($orderItem, $changeItemQuantity->quantity());
        $this->orderProcessor->process($order);
    }
}
