<?php

namespace App\Processor;

use Doctrine\Persistence\ObjectRepository;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Provider\ZoneProviderInterface;
use Sylius\Component\Core\Taxation\Strategy\TaxCalculationStrategyInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Registry\PrioritizedServiceRegistryInterface;
use Webmozart\Assert\Assert;

/**
 * Class NonEUTaxAdjustmentProcessor.
 */
class NonEUTaxAdjustmentProcessor implements OrderProcessorInterface
{
    public function __construct(
        private readonly ZoneProviderInterface $defaultTaxZoneProvider,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly PrioritizedServiceRegistryInterface $strategyRegistry,
        private readonly ObjectRepository $taxRateRepository,
        private readonly string $nonEUZone,
        private readonly string $frenchVATCode
    ) {
    }

    public function process(BaseOrderInterface $order): void
    {
        /* @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);

        $zone = $this->getTaxZone($order);

        if (null === $zone || 0 !== strcmp((string) $zone->getCode(), $this->nonEUZone)) {
            return;
        }

        $this->clearTaxes($order);
        if ($order->isEmpty()) {
            return;
        }

        $order->setItemsTotal($this->getFrenchVATRate());

        /** @var TaxCalculationStrategyInterface $strategy */
        foreach ($this->strategyRegistry->all() as $strategy) {
            if ($strategy->supports($order, $zone)) {
                $strategy->applyTaxes($order, $zone);

                return;
            }
        }
    }

    private function getTaxZone(OrderInterface $order): ?ZoneInterface
    {
        $shippingAddress = $order->getShippingAddress();
        $zone = null;

        if (null !== $shippingAddress) {
            $zone = $this->zoneMatcher->match($shippingAddress, Scope::TAX);
        }

        return $zone ?: $this->defaultTaxZoneProvider->getZone($order);
    }

    private function getFrenchVATRate()
    {
        $frenchVATRate = $this->taxRateRepository->findOneBy(['code' => $this->frenchVATCode]);

        return $frenchVATRate->getAmount();
    }

    private function clearTaxes(BaseOrderInterface $order): void
    {
        $order->removeAdjustments(AdjustmentInterface::TAX_ADJUSTMENT);
        foreach ($order->getItems() as $item) {
            $item->removeAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT);
        }
    }
}
