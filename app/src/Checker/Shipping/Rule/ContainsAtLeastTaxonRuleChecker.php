<?php

declare(strict_types=1);

namespace App\Checker\Shipping\Rule;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Shipping\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;

class ContainsAtLeastTaxonRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'contains_at_least_taxon';

    public function isEligible(ShippingSubjectInterface $subject, array $configuration): bool
    {
        if (!isset($configuration['taxon'])) {
            throw new \InvalidArgumentException('Configuration is missing the taxon code');
        }

        /** @var ProductVariant $shippable */
        foreach ($subject->getShippables() as $shippable) {
            /** @var Product $product */
            $product = $shippable->getProduct();

            $matches = $product->getTaxons()
                ->filter(static fn (TaxonInterface $taxon): bool => $taxon->getCode() === $configuration['taxon']);

            if (!$matches->isEmpty() || $product->getMainTaxon()?->getCode() === $configuration['taxon']) {
                return true;
            }
        }

        return false;
    }
}
