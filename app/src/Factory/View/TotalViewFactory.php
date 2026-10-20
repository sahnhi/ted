<?php

namespace App\Factory\View;

use App\View\TotalsView;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Provider\ZoneProviderInterface;
use Sylius\Component\Core\Taxation\Strategy\TaxCalculationStrategyInterface;
use Sylius\Component\Registry\PrioritizedServiceRegistryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class TotalViewFactory implements TotalViewFactoryInterface
{
    public function __construct(
        private readonly RepositoryInterface $taxRate,
        private readonly ZoneProviderInterface $defaultTaxZoneProvider,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly PrioritizedServiceRegistryInterface $strategyRegistry
    ) {
    }

    public function create(OrderInterface $cart): TotalsView
    {
        $amountGiftCard = 0;
        foreach ($cart->getGiftCards() as $giftCard) {
            $amountGiftCard += $giftCard->getAmount();
        }

        $promotionTotal = $cart->getOrderPromotionTotal();
        $totalPrice = $cart->getTotal();

        $amountUsed = $cart->getItemsTotal() - $cart->getTotal();
        $amountLeft = ($amountGiftCard - $amountUsed);
        $totalsView = new TotalsView();
        $totalsView->promotion = $promotionTotal;
        $totalsView->total = $totalPrice;
        $totalsView->undiscountedPrice = $totalPrice + abs($promotionTotal);
        $totalsView->items = $cart->getItemsTotal();
        $totalsView->shipping = $cart->getShippingTotal();
        $totalsView->taxes = $cart->getTaxTotal();
        $totalsView->ecotax = $cart->getEcotaxTotal();
        $totalsView->amountGiftCardUsed = $amountUsed;
        $totalsView->amountGiftCardLeft = $amountLeft;

        $shippingAddress = $cart->getShippingAddress();
        $zone = null;

        if (null !== $shippingAddress) {
            $zone = $this->zoneMatcher->match($shippingAddress, Scope::TAX);
        }

        $taxZone = $zone ?: $this->defaultTaxZoneProvider->getZone($cart);

        $taxRate = 0;

        /** @var TaxCalculationStrategyInterface $strategy */
        foreach ($this->strategyRegistry->all() as $strategy) {
            if ($strategy->supports($cart, $taxZone)) {
                if ($strategy->supports($cart, $taxZone)) {
                    $taxRate = $this->taxRate->findOneBy(['zone' => $taxZone])->getAmount();
                }
            }
        }

        $totalsView->taxesRate = (float) number_format($taxRate, 3);

        return $totalsView;
    }
}
