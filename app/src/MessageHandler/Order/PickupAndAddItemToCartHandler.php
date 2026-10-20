<?php

declare(strict_types=1);

namespace App\MessageHandler\Order;

use App\Doctrine\Entity\Sylius\Order;
use App\Message\Order\AddItemToCart;
use App\Message\Order\PickupAndAddItemToCart;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ApiBundle\Command\Cart\PickupCart;
use Sylius\Bundle\ApiBundle\CommandHandler\PickupCartHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PickupAndAddItemToCartHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly PickupCartHandler $pickupCartHandler,
        private readonly AddItemToCartHandler $addItemToCartHandler,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(PickupAndAddItemToCart $message): Order
    {
        $pickupCartMessage = new PickupCart(null, $message->localeCode);
        $pickupCartMessage->setChannelCode($message->getChannelCode());

        /** @var Order $order */
        $order = ($this->pickupCartHandler)($pickupCartMessage);

        // We need to flush as the PickupOrderHandler only persists
        $this->entityManager->flush();

        $addItemToCartMessage = new AddItemToCart(
            $message->productVariantCode,
            $message->quantity,
            $message->getAddons()
        );
        $addItemToCartMessage->setOrderTokenValue($order->getTokenValue());

        /** @var Order $order */
        $order = ($this->addItemToCartHandler)($addItemToCartMessage);

        return $order;
    }
}
