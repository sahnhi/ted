<?php

declare(strict_types=1);

namespace App\MessageHandler\Order;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Message\Order\SendCartByEmail;
use App\Services\Drip\DripApiInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SendCartByEmailHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly DripApiInterface $dripApi,
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    public function __invoke(SendCartByEmail $sendCartByEmail): void
    {
        $order = $this->orderRepository->findCartByTokenValue($sendCartByEmail->getOrderTokenValue());
        if (!$order instanceof Order) {
            return;
        }

        $this->dripApi->sendOrder($order);
        $this->dripApi->sendTag($sendCartByEmail->getEmail(), ['savedCart']);
    }
}
