<?php

namespace App\Operator\GiftCard;

use App\Factory\GiftCard\GiftCardFactory;
use App\Model\GiftCardAddress;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusGiftCardPlugin\EmailManager\GiftCardEmailManagerInterface;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderItemUnitInterface;
use Setono\SyliusGiftCardPlugin\Model\ProductInterface;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Webmozart\Assert\Assert;

/**
 * This class' responsibility is to operate on gift cards bought on an order
 * It does NOT handle gift cards used to buy an order.
 */
final class OrderGiftCardOperator implements OrderGiftCardOperatorInterface
{
    public function __construct(
        private readonly GiftCardFactory $giftCardFactory,
        private readonly GiftCardRepositoryInterface $giftCardRepository,
        private readonly EntityManagerInterface $giftCardManager,
        private readonly GiftCardEmailManagerInterface $giftCardOrderEmailManager
    ) {
    }

    public function create(OrderInterface $order, GiftCardAddress $giftCardAddress): void
    {
        $items = self::getOrderItemsThatAreGiftCards($order);

        if (0 === count($items)) {
            return;
        }

        foreach ($items as $item) {
            /** @var OrderItemUnitInterface $unit */
            foreach ($item->getUnits() as $unit) {
                $giftCard = $this->giftCardFactory->createFromOrderAndGiftCardAddress($unit, $giftCardAddress);

                $this->giftCardManager->persist($giftCard);
            }
        }

        $this->giftCardManager->flush();
    }

    public function enable(OrderInterface $order): void
    {
        $giftCards = $this->getGiftCards($order);

        if (0 === count($giftCards)) {
            return;
        }

        foreach ($giftCards as $giftCard) {
            $giftCard->enable();
        }

        $this->giftCardManager->flush();
    }

    /**
     * Calls when Order this GiftCardCode was bought at
     * become cancelled.
     */
    public function disable(OrderInterface $order): void
    {
        $giftCards = $this->getGiftCards($order);

        if (0 === count($giftCards)) {
            return;
        }

        foreach ($giftCards as $giftCard) {
            $giftCard->disable();
        }

        $this->giftCardManager->flush();
    }

    public function send(OrderInterface $order): void
    {
        $giftCards = $this->getGiftCards($order);

        if (0 === count($giftCards)) {
            return;
        }

        $this->giftCardOrderEmailManager->sendEmailWithGiftCardsFromOrder($order, $giftCards);
    }

    /**
     * Returns all the gift cards that were bought on the given order.
     *
     * @return GiftCardInterface[]
     */
    private function getGiftCards(OrderInterface $order): array
    {
        $giftCards = [];

        $items = self::getOrderItemsThatAreGiftCards($order);
        foreach ($items as $item) {
            /** @var OrderItemUnitInterface $unit */
            foreach ($item->getUnits() as $unit) {
                $giftCard = $this->giftCardRepository->findOneByOrderItemUnit($unit);
                if (null === $giftCard) {
                    continue;
                }

                $giftCards[] = $giftCard;
            }
        }

        return $giftCards;
    }

    /**
     * @return Collection|OrderItemInterface[]
     */
    private static function getOrderItemsThatAreGiftCards(OrderInterface $order): Collection
    {
        return $order->getItems()->filter(static function (OrderItemInterface $item): bool {
            /** @var ProductInterface|null $product */
            $product = $item->getProduct();

            Assert::isInstanceOf($product, ProductInterface::class);

            return $product->isGiftCard();
        });
    }
}
