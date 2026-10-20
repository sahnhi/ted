<?php

namespace App\Factory;

use Sylius\Component\Core\Factory\PromotionActionFactory as BasePromotionActionFactory;
use Sylius\Component\Core\Factory\PromotionActionFactoryInterface;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionActionInterface;

final class PromotionActionFactory implements PromotionActionFactoryInterface
{
    public function __construct(
        private readonly BasePromotionActionFactory $decoratedFactory
    ) {
    }

    public function createNew(): PromotionActionInterface
    {
        return $this->decoratedFactory->createNew();
    }

    public function createFixedDiscount(int $amount, string $channelCode): PromotionActionInterface
    {
        return $this->decoratedFactory->createFixedDiscount($amount, $channelCode);
    }

    public function createUnitFixedDiscount(int $amount, string $channelCode): PromotionActionInterface
    {
        return $this->decoratedFactory->createUnitFixedDiscount($amount, $channelCode);
    }

    public function createPercentageDiscount(float $percentage): PromotionActionInterface
    {
        return $this->decoratedFactory->createPercentageDiscount($percentage);
    }

    public function createUnitPercentageDiscount(float $percentage, string $channelCode): PromotionActionInterface
    {
        return $this->decoratedFactory->createUnitPercentageDiscount($percentage, $channelCode);
    }

    public function createShippingPercentageDiscount(float $percentage): PromotionActionInterface
    {
        return $this->decoratedFactory->createShippingPercentageDiscount($percentage);
    }

    public function createNewForPromotion(PromotionInterface $promotion): PromotionActionInterface
    {
        $action = $this->createNew();
        $action->setPromotion($promotion);

        return $action;
    }

    public function updateFromExisting(PromotionActionInterface $newAction, PromotionActionInterface $oldAction): PromotionActionInterface
    {
        $newAction->setType($oldAction->getType());
        $newAction->setConfiguration($oldAction->getConfiguration());

        return $newAction;
    }
}
