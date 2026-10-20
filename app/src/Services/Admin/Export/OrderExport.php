<?php

declare(strict_types=1);

namespace App\Services\Admin\Export;

use App\Doctrine\Entity\OrderReturnItem;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Services\Channel\ChannelCode;
use Doctrine\Persistence\ObjectManager;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class OrderExport
{
    public const MAX_ITEM_EXPORT = 10000;

    public function __construct(
        private readonly ObjectManager $objectManager
    ) {
    }

    public function generateExport(ResourceGridView $resources, array $data): StreamedResponse
    {
        $data[] = [
            'Channel',
            'Number',
            'Date',
            'Customer',
            'State',
            'Payment State',
            'Shipping State',
            'Delivery Slot',
            'Deliver',
            'Total',
            'Currency Code',
            'Bundle',
            'Products (Quantity)',
            'Problems',
            'Commercial Offer',
        ];

        /** @var Pagerfanta $pager */
        $pager = $resources->getData();
        $firstPage = true;

        do {
            if (!$firstPage) {
                $pager->setCurrentPage($pager->getNextPage());
            }

            $firstPage = false;
            $rows = $pager->getCurrentPageResults();

            /** @var Order $row */
            foreach ($rows as $row) {
                $products = [];
                $problems = $commercialOffer = $deliverySlot = $deliver = '';
                $isBundle = false;
                /** @var OrderItem $item */
                foreach ($row->getItems() as $item) {
                    /** @var ProductVariantInterface $variant */
                    $variant = $item->getVariant();
                    if ($variant->isBundle()) {
                        $isBundle = true;
                        foreach ($variant->getBundleAssociations() as $bundle) {
                            $quantity = $item->getQuantity() * $bundle->getQuantity();
                            $products[] = $bundle->getVariant()->getCode() . ' (' . $quantity . ')';
                        }
                    } else {
                        $products[] = $item->getVariant()->getCode() . ' (' . $item->getQuantity() . ')';
                    }
                }

                if (!empty($row->getShipments()[0])) {
                    /** @var Shipment $shipment */
                    $shipment = $row->getShipments()[0];
                    if (null !== $shipment->getStartDeliverySlot() && null !== $shipment->getEndDeliverySlot()) {
                        $deliverySlot .= $shipment->getStartDeliverySlot()->format('d/m/Y');
                        $deliverySlot .= ' (' . $shipment->getStartDeliverySlot()->format('H:i') . ' - ' . $shipment->getEndDeliverySlot()->format('H:i') . ')';
                        $deliver = $shipment->getMethod()->getCode();
                    }
                }

                $products = implode(';', $products);
                $order = [
                    $row->getChannel()->getName(),
                    $row->getNumber(),
                    null !== $row->getCheckoutCompletedAt() ? $row->getCheckoutCompletedAt()->format('d/m/Y H:i:s') : '',
                    $row->getCustomer(),
                    $row->getState(),
                    $row->getPaymentState(),
                    $row->getShippingState(),
                    $deliverySlot,
                    $deliver,
                    $row->getTotal() / 100,
                    $row->getCurrencyCode(),
                    (true === $isBundle) ? 'Yes' : 'No',
                    $products,
                ];

                foreach ($row->getOrderReason() as $orderReason) {
                    if (null !== $orderReason->getReason()) {
                        $problems .= $orderReason->getReason()->getName();
                        $problems .= ' (' . $orderReason->getCreatedAt()->format('d/m/Y') . '). ';
                    }
                    if (null !== $orderReason->getCommercialOffer()) {
                        $commercialOffer .= $orderReason->getCommercialOffer()->getName() . '. ';
                    }
                }
                $order[] = trim($problems);
                $order[] = trim($commercialOffer);
                $data[] = $order;

                $this->objectManager->detach($row);
            }
        } while ($pager->hasNextPage());

        return (new ExportCsv())->generateCsv($data, 'orders');
    }

    public function generateLogisticExport(ResourceGridView $resources, array $data): StreamedResponse
    {
        $data[] = [
            'Channel',
            'Number',
            'Date',
            'Customer',
            'State',
            'Payment State',
            'Shipping State',
            'Delivery Slot',
            'Deliver',
            'Total',
            'Currency Code',
            'Bundle',
            'Products (Quantity)',
            'Problems',
            'Commercial Offer',
            'Destination',
        ];

        /** @var Pagerfanta $pager */
        $pager = $resources->getData();
        $firstPage = true;

        do {
            if (!$firstPage) {
                $pager->setCurrentPage($pager->getNextPage());
            }

            $firstPage = false;
            $rows = $pager->getCurrentPageResults();

            /** @var Order $row */
            foreach ($rows as $row) {
                $products = [];
                $problems = $commercialOffer = $deliverySlot = $deliver = '';
                $isBundle = false;
                /** @var OrderItem $item */
                foreach ($row->getItems() as $item) {
                    /** @var ProductVariantInterface $variant */
                    $variant = $item->getVariant();
                    if ($variant->isBundle()) {
                        $isBundle = true;
                        foreach ($variant->getBundleAssociations() as $bundle) {
                            $quantity = $item->getQuantity() * $bundle->getQuantity();
                            $products[] = $bundle->getVariant()->getCode() . ' (' . $quantity . ')';
                        }
                    } else {
                        $products[] = $item->getVariant()->getCode() . ' (' . $item->getQuantity() . ')';
                    }
                }

                if (!empty($row->getShipments()[0])) {
                    /** @var Shipment $shipment */
                    $shipment = $row->getShipments()[0];
                    if (null !== $shipment->getStartDeliverySlot() && null !== $shipment->getEndDeliverySlot()) {
                        $deliverySlot .= $shipment->getStartDeliverySlot()->format('d/m/Y');
                        $deliverySlot .= ' (' . $shipment->getStartDeliverySlot()->format('H:i') . ' - ' . $shipment->getEndDeliverySlot()->format('H:i') . ')';
                        $deliver = $shipment->getMethod()->getCode();
                    }
                }

                $destination = null;
                if (ChannelCode::CHANNEL_RETURN === $row->getChannel()->getCode()) {
                    $orderReturn = $row->getOrderReturn();
                    if (null === $orderReturn) {
                        continue;
                    }

                    /** @var OrderReturnItem $returnItem */
                    $returnItem = $orderReturn->getItems()->first();
                    $destination = $returnItem->getDestination();
                }

                $products = implode(';', $products);
                $order = [
                    $row->getChannel()->getName(),
                    $row->getNumber(),
                    null !== $row->getCheckoutCompletedAt() ? $row->getCheckoutCompletedAt()->format('d/m/Y H:i:s') : '',
                    $row->getCustomer(),
                    $row->getState(),
                    $row->getPaymentState(),
                    $row->getShippingState(),
                    $deliverySlot,
                    $deliver,
                    $row->getTotal() / 100,
                    $row->getCurrencyCode(),
                    (true === $isBundle) ? 'Yes' : 'No',
                    $products,
                ];

                foreach ($row->getOrderReason() as $orderReason) {
                    if (null !== $orderReason->getReason()) {
                        $problems .= $orderReason->getReason()->getName();
                        $problems .= ' (' . $orderReason->getCreatedAt()->format('d/m/Y') . '). ';
                    }
                    if (null !== $orderReason->getCommercialOffer()) {
                        $commercialOffer .= $orderReason->getCommercialOffer()->getName() . '. ';
                    }
                }
                $order[] = trim($problems);
                $order[] = trim($commercialOffer);
                $order[] = $destination;
                $data[] = $order;

                $this->objectManager->detach($row);
            }
        } while ($pager->hasNextPage());

        return (new ExportCsv())->generateCsv($data, 'orders');
    }
}
