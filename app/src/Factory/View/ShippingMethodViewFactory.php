<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\ServiceRulesRepository;
use App\View\ShippingMethodTransmissionView;
use App\View\ShippingMethodView;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Registry\ServiceRegistry;
use Sylius\Component\Shipping\Calculator\CalculatorInterface;

final class ShippingMethodViewFactory implements ShippingMethodViewFactoryInterface
{
    public function __construct(
        private readonly ServiceRegistry $calculatorRegistry,
        private readonly PriceViewFactoryInterface $priceViewFactory,
        private readonly ServiceRulesRepository $serviceRulesRepository
    ) {
    }

    public function create(ShipmentInterface $shipment, string $locale, string $currency): ShippingMethodView
    {
        return $this->createWithShippingMethod($shipment, $shipment->getMethod(), $locale, $currency);
    }

    public function createWithShippingMethod(ShipmentInterface $shipment, ShippingMethodInterface $shippingMethod, string $locale, string $currency): ShippingMethodView
    {
        /** @var CalculatorInterface $calculator */
        $calculator = $this->calculatorRegistry->get($shippingMethod->getCalculator());

        $shippingMethodView = new ShippingMethodView();
        $shippingMethodView->code = $shippingMethod->getCode();
        $shippingMethodView->name = $shippingMethod->getTranslation($locale)->getName();
        $shippingMethodView->description = $shippingMethod->getTranslation($locale)->getDescription();
        $shippingMethodView->price = $this->priceViewFactory->create(
            $calculator->calculate($shipment, $shippingMethod->getConfiguration()),
            $currency
        );

        return $shippingMethodView;
    }

    public function createTransmission(ShipmentInterface $shipment, ShippingMethodInterface $shippingMethod, string $locale, string $currency): ShippingMethodTransmissionView
    {
        $transportTime = $shippingMethod->getTransportTime();

        if ($shippingMethod->getIdShippingMethodBalou() !== $shipment->getIdShippingMethodBalou()) {
            $serviceRules = $this->serviceRulesRepository->findOneBy(['idShippingMethodBalou' => $shipment->getIdShippingMethodBalou()]);

            if (null !== $serviceRules) {
                $transportTime = $serviceRules->getTransportTime();
            }
        }

        $shippingMethodTransmissionView = new ShippingMethodTransmissionView();
        $shippingMethodTransmissionView->code = $shippingMethod->getCode();
        $shippingMethodTransmissionView->shippingTimeDate = $shippingMethod->getShippingTimeDate();
        $shippingMethodTransmissionView->transportTime = $transportTime;

        return $shippingMethodTransmissionView;
    }
}
