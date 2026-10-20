<?php

namespace App\Factory;

use Sylius\Component\Core\Factory\PromotionRuleFactory as BasePromotionRuleFactory;
use Sylius\Component\Core\Factory\PromotionRuleFactoryInterface;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;

final class PromotionRuleFactory implements PromotionRuleFactoryInterface
{
    public function __construct(
        private readonly BasePromotionRuleFactory $decoratedFactory
    ) {
    }

    public function createNew(): PromotionRuleInterface
    {
        return $this->decoratedFactory->createNew();
    }

    public function createCartQuantity(int $count): PromotionRuleInterface
    {
        return $this->decoratedFactory->createCartQuantity($count);
    }

    public function createItemTotal(string $channelCode, int $amount): PromotionRuleInterface
    {
        return $this->decoratedFactory->createItemTotal($channelCode, $amount);
    }

    public function createHasTaxon(array $taxons): PromotionRuleInterface
    {
        return $this->decoratedFactory->createHasTaxon($taxons);
    }

    public function createItemsFromTaxonTotal(string $channelCode, string $taxonCode, int $amount): PromotionRuleInterface
    {
        return $this->decoratedFactory->createItemsFromTaxonTotal($channelCode, $taxonCode, $amount);
    }

    public function createNthOrder(int $nth): PromotionRuleInterface
    {
        return $this->decoratedFactory->createNthOrder($nth);
    }

    public function createContainsProduct(string $productCode): PromotionRuleInterface
    {
        return $this->decoratedFactory->createContainsProduct($productCode);
    }

    public function createNewForPromotion(PromotionInterface $promotion): PromotionRuleInterface
    {
        $rule = $this->createNew();
        $rule->setPromotion($promotion);

        return $rule;
    }

    public function updateFromExisting(PromotionRuleInterface $newRule, PromotionRuleInterface $oldRule): PromotionRuleInterface
    {
        $newRule->setType($oldRule->getType());
        $newRule->setConfiguration($oldRule->getConfiguration());

        return $newRule;
    }
}
