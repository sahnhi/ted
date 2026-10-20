<?php

declare(strict_types=1);

namespace App\Promotion\Checker\Rule;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

final class RefuseTaxonRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'refuse_taxon';

    /**
     * @throws UnsupportedTypeException
     */
    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!isset($configuration['taxons'])) {
            return false;
        }

        if (!$subject instanceof OrderInterface) {
            throw new UnsupportedTypeException($subject, OrderInterface::class);
        }

        /** @var OrderItemInterface $item */
        foreach ($subject->getItems() as $item) {
            if ($this->hasProductRefusedTaxon($item->getProduct(), $configuration)) {
                return false;
            }
        }

        return true;
    }

    private function hasProductRefusedTaxon(ProductInterface $product, array $configuration): bool
    {
        foreach ($product->getTaxons() as $taxon) {
            if (in_array($taxon->getCode(), $configuration['taxons'], true)) {
                return true;
            }
        }

        return false;
    }
}
