<?php

namespace App\Handler;

use App\Business\Command\DropCart;
use App\Doctrine\Repository\Sylius\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Webmozart\Assert\Assert;

final class DropCartHandler
{
    public function __construct(
        private readonly OrderRepository $cartRepository,
        private readonly EntityManagerInterface $cartManager
    ) {
    }

    public function handle(DropCart $dropCart)
    {
        /** @var OrderInterface $cart */
        $cart = $this->cartRepository->findOneBy(['tokenValue' => $dropCart->orderToken()]);
        Assert::notNull($cart, sprintf('Order with %s token has not been found.', $dropCart->orderToken()));
        Assert::same(BaseOrderInterface::STATE_CART, $cart->getState());

        $this->cartManager->remove($cart);
    }
}
