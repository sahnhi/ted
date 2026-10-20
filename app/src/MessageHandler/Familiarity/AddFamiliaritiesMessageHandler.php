<?php

declare(strict_types=1);

namespace App\MessageHandler\Familiarity;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\FamiliarityRepositoryInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Model\Familiarity\AddFamiliarities;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddFamiliaritiesMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly FamiliarityRepositoryInterface $familiarityRepository,
    ) {
    }

    public function __invoke(AddFamiliarities $addFamiliaritiesMessage): Customer
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->findOneByTokenValue($addFamiliaritiesMessage->tokenValue);
        if (null === $order) {
            throw new \InvalidArgumentException(sprintf('Order not found for token: %s', $addFamiliaritiesMessage->tokenValue));
        }

        /** @var Customer|null $customer */
        $customer = $order->getCustomer();
        if (null === $customer) {
            throw new \InvalidArgumentException('Order has no customer');
        }

        foreach ($addFamiliaritiesMessage->familiarities as $familiarityCode) {
            $familiarity = $this->familiarityRepository->findOneByCode($familiarityCode);

            if (null !== $familiarity) {
                $customer->addFamiliarity($familiarity);
            }
        }

        return $customer;
    }
}
