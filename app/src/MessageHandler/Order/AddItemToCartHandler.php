<?php

declare(strict_types=1);

namespace App\MessageHandler\Order;

use App\Message\Order\AddItemToCart;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AddItemToCartHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly MessageHandlerInterface $decoratedAddItemToCartHandler,
    ) {
    }

    public function __invoke(AddItemToCart $command): OrderInterface
    {
        $order = ($this->decoratedAddItemToCartHandler)($command);

        foreach ($command->getAddons() as $addon) {
            $addonCommand = new AddItemToCart($addon['productVariant'], $addon['quantity']);
            $addonCommand->setOrderTokenValue($command->getOrderTokenValue());

            ($this->decoratedAddItemToCartHandler)($addonCommand);
        }

        return $order;
    }
}
