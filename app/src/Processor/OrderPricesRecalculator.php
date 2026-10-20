<?php

declare(strict_types=1);

namespace App\Processor;

use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\Promotion;
use App\Exception\UnsupportedPromotionActionException;
use App\Specification\Promotion\PromotionIsAdvantageousSpecification;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionEligibilityCheckerInterface;
use Sylius\Component\Promotion\Provider\PreQualifiedPromotionsProviderInterface;
use Webmozart\Assert\Assert;

class OrderPricesRecalculator implements OrderProcessorInterface
{
    public function __construct(
        private readonly ProductVariantPricesCalculatorInterface $productVariantPriceCalculator,
        private readonly PreQualifiedPromotionsProviderInterface $preQualifiedPromotionsProvider,
        private readonly PromotionEligibilityCheckerInterface $promotionEligibilityChecker,
        private readonly PromotionIsAdvantageousSpecification $promotionIsAdvantageousSpecification,
    ) {
    }

    public function process(BaseOrderInterface $order): void
    {
        /* @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);

        $channel = $order->getChannel();

        $availablePromotions = $this->preQualifiedPromotionsProvider->getPromotions($order);

        foreach ($order->getItems() as $item) {
            if ($item->isImmutable()) {
                continue;
            }

            /** @var Promotion $promotion */
            foreach ($availablePromotions as $promotion) {
                if (!$this->promotionEligibilityChecker->isEligible($order, $promotion)) {
                    continue;
                }

                try {
                    if (($this->promotionIsAdvantageousSpecification)($order, $item, $promotion)) {
                        $item->setUnitPrice($this->productVariantPriceCalculator->calculateOriginal(
                            $item->getVariant(),
                            ['channel' => $channel]
                        ));

                        continue 2;
                    }
                } catch (UnsupportedPromotionActionException) {
                }
            }

            $item->setUnitPrice($this->productVariantPriceCalculator->calculate(
                $item->getVariant(),
                ['channel' => $channel]
            ));
        }
    }
}
