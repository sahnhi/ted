<?php

namespace App\Factory;

use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class PromotionFactory implements FactoryInterface
{
    public function __construct(
        private readonly FactoryInterface $decoratedFactory,
        private readonly PromotionRuleFactory $ruleFactory,
        private readonly PromotionActionFactory $actionFactory
    ) {
    }

    public function createNew(): PromotionInterface
    {
        return $this->decoratedFactory->createNew();
    }

    public function updateFromExisting(PromotionInterface $newPromotion, PromotionInterface $oldPromotion): PromotionInterface
    {
        $newPromotion->setCouponBased($oldPromotion->isCouponBased());
        $newPromotion->setExclusive($oldPromotion->isExclusive());

        foreach ($oldPromotion->getChannels() as $channel) {
            $newPromotion->addChannel($channel);
        }

        $newPromotion->setCode($oldPromotion->getCode() . '-copy');
        $newPromotion->setName($oldPromotion->getName());
        $newPromotion->setDescription($oldPromotion->getDescription());
        $newPromotion->setPriority($oldPromotion->getPriority());
        $newPromotion->setUsageLimit($oldPromotion->getUsageLimit());
        $newPromotion->setStartsAt($oldPromotion->getStartsAt());
        $newPromotion->setEndsAt($oldPromotion->getEndsAt());

        foreach ($oldPromotion->getRules() as $rule) {
            $newRule = $this->ruleFactory->createNewForPromotion($newPromotion);
            $newRule = $this->ruleFactory->updateFromExisting($newRule, $rule);
            $newPromotion->addRule($newRule);
        }

        foreach ($oldPromotion->getActions() as $action) {
            $newAction = $this->actionFactory->createNewForPromotion($newPromotion);
            $newAction = $this->actionFactory->updateFromExisting($newAction, $action);
            $newPromotion->addAction($newAction);
        }

        return $newPromotion;
    }
}
