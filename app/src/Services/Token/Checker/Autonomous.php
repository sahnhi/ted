<?php

namespace App\Services\Token\Checker;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use App\Services\Token\AbstractChecker;
use App\Services\Token\TokenException;

class Autonomous extends AbstractChecker
{
    private readonly \DateTimeInterface $date;

    public function __construct(
        private readonly OrderRepository $orderRepository
    ) {
        $this->date = new \DateTime();
        $this->date->sub(new \DateInterval('P30D'));
    }

    /**
     * @throws TokenException
     * @throws \Exception
     */
    public function check(string $token, ?string $email = null): void
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $token]);

        if (null === $order) {
            throw new TokenException(sprintf('Order not found for token : %s', $token));
        }
        if (ReturnTypeServiceInterface::RETURN_TYPE_AUTONOMOUS !== $order->getReturnType(true)) {
            throw new TokenException(sprintf('Order %s is of wrong type', $order->getNumber()));
        }
        if ($order->getCustomer()->getEmail() !== $email) {
            throw new TokenException(sprintf('Order %s does not match customer %s', $order->getNumber(), $email));
        }

        if ($order->getCreatedAt() < $this->date) {
            throw new \Exception('Token expired');
        }
    }
}
