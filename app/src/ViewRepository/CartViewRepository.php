<?php

namespace App\ViewRepository;

use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Exception\Api\Cart\InvalidBodyException;
use App\Factory\View\CartViewFactory;
use App\View\CartSummaryView;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class CartViewRepository implements CartViewRepositoryInterface
{
    public function __construct(
        private readonly OrderRepository $cartRepository,
        private readonly CartViewFactory $cartViewFactory
    ) {
    }

    public function getOneByToken(string $orderToken, bool $withVariants = false): CartSummaryView
    {
        /** @var OrderInterface $cart */
        $cart = $this->cartRepository->getOneByTokenValueHydrated($orderToken);
        Assert::notNull($cart, 'Cart with given id does not exists');

        return $this->cartViewFactory->create($cart, $cart->getLocaleCode(), $withVariants);
    }

    public function getOneByEmailAndNumber(string $email, string $number, bool $withVariants = false): CartSummaryView
    {
        /** @var OrderInterface $cart */
        $cart = $this->cartRepository->getCurrentByEmailAndNumberValueHydrated($email, $number);
        Assert::notNull($cart, 'Cart with given email and number not found');

        /** @var Shipment $shipment */
        $shipment = $cart->getShipments()->first();
        if (null !== $shipment && new \DateTime() > $shipment->getEndDeliverySlot()) {
            throw new InvalidBodyException('Cart is already delivered');
        }

        return $this->cartViewFactory->create($cart, $cart->getLocaleCode(), $withVariants);
    }
}
