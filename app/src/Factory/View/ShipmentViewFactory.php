<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\Shipment;
use App\Services\Date\DateHelper;
use App\View\ShipmentTransmissionView;
use App\View\ShipmentView;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ShipmentViewFactory implements ShipmentViewFactoryInterface
{
    public function __construct(
        private readonly ShippingMethodViewFactoryInterface $shippingMethodViewFactory,
        private DateHelper $dateHelper,
        private readonly RepositoryInterface $serviceRulesRepository
    ) {
    }

    public function create(ShipmentInterface $shipment, string $locale): ShipmentView
    {
        /** @var OrderInterface $order */
        $order = $shipment->getOrder();
        $shipmentView = new ShipmentView();
        $shipmentView->state = $shipment->getState();
        $shipmentView->startDeliverySlot = $shipment->getStartDeliverySlot();
        $shipmentView->endDeliverySlot = $shipment->getEndDeliverySlot();
        $shipmentView->method = $this->shippingMethodViewFactory->create($shipment, $locale, $order->getCurrencyCode());
        $shipmentView->tracking = $shipment->getTracking();

        return $shipmentView;
    }

    public function createTransmission(ShipmentInterface $shipment, string $locale): ShipmentTransmissionView
    {
        $order = $shipment->getOrder();
        $shipmentTransmissionView = new ShipmentTransmissionView();
        $shipmentTransmissionView->startDeliverySlot = $shipment->getStartDeliverySlot();
        $shipmentTransmissionView->endDeliverySlot = $shipment->getEndDeliverySlot();
        $shipmentTransmissionView->checkoutCompletedAt = $order->getCheckoutCompletedAt();
        $shipmentTransmissionView->method = $this->shippingMethodViewFactory->createTransmission($shipment, $shipment->getMethod(), $locale, $order->getCurrencyCode());
        $shipmentTransmissionView->dateHelper = $this->dateHelper;
        $shipmentTransmissionView->shippingMethod = $shipment->getMethod();

        if ($shipment->getIdShippingMethodBalou() !== $shipment->getMethod()->getIdShippingMethodBalou()) {
            $shipmentTransmissionView->serviceRules = $this->serviceRulesRepository->findOneByIdShippingMethodBalou($shipment->getIdShippingMethodBalou());
        }

        $shipment->setShippingDate(\DateTime::createFromFormat('ymd', $shipmentTransmissionView->getShippingDate(true)));

        /* @var Shipment $shipment */
        $shipmentTransmissionView->relayId = $shipment->getRelayId();

        return $shipmentTransmissionView;
    }
}
