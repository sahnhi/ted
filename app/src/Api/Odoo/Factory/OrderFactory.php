<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\AbstractAddress;
use App\Api\Odoo\Model\BillingAddress;
use App\Api\Odoo\Model\Customer;
use App\Api\Odoo\Model\Item;
use App\Api\Odoo\Model\Order;
use App\Api\Odoo\Model\OrderInterface;
use App\Api\Odoo\Model\OrderReturn;
use App\Api\Odoo\Model\OrderShipment;
use App\Api\Odoo\Model\OrderStock;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Provider\TaxRateProviderInterface;
use App\Services\Payment\TransactionIdFinder;
use App\Services\Shipment\LogisticItemsInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

class OrderFactory implements OrderFactoryInterface
{
    public function __construct(
        private readonly CustomerFactoryInterface $customerFactory,
        private readonly AddressFactoryInterface $addressFactory,
        private readonly DeliveryDateFactoryInterface $deliveryDateFactory,
        private readonly ItemFactoryInterface $itemFactory,
        private readonly TransactionIdFinder $transactionIdFinder,
        private readonly LogisticItemsInterface $logisticItems,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly TaxRateProviderInterface $taxRateProvider,
        private readonly RepositoryInterface $productVariantRepository,
        private readonly RepositoryInterface $productComboRepository,
        private readonly ShipmentItemFactoryInterface $shipmentItemFactory,
    ) {
    }

    public function createNew(BaseOrder $syliusOrder, string $className = Order::class): OrderInterface
    {
        /** @var Customer $customer */
        $customer = $this->customerFactory->createNew($syliusOrder);
        /** @var AbstractAddress $shippingAddress */
        $shippingAddress = $this->addressFactory->createNew($syliusOrder->getShippingAddress(), $syliusOrder->getCustomer());
        /** @var AbstractAddress $invoicingAddress */
        $invoicingAddress = $this->addressFactory->createNew($syliusOrder->getBillingAddress() ?? $syliusOrder->getShippingAddress(), $syliusOrder->getCustomer(), BillingAddress::class);

        foreach (['firstname', 'lastname'] as $field) {
            if (empty($customer->$field)) {
                $customer->$field = $shippingAddress->$field ?: $invoicingAddress->$field;
            }
        }

        $deliveryDate = null;
        if ($syliusOrder->getShipments()->last()) {
            $deliveryDate = $this->deliveryDateFactory->createNew($syliusOrder->getShipments()->last());
        }

        $items = $this->initItems($syliusOrder);

        $payment = $syliusOrder->getLastPayment(PaymentInterface::STATE_COMPLETED);
        if (null !== $payment) {
            $paymentId = $this->transactionIdFinder->getTransactionId($payment);
        }

        $taxRate = $this->taxRateProvider->provideTaxRateFromZone($syliusOrder);
        $taxRateCode = null !== $taxRate ? $taxRate->getCode() : null;

        $class = new $className($syliusOrder, $items, $shippingAddress, $invoicingAddress, $deliveryDate, $customer, $paymentId ?? null, $taxRateCode);
        Assert::isInstanceOf($class, OrderInterface::class);

        return $class;
    }

    public function createNewOrderShipment(BaseOrder $syliusOrder, string $className = OrderShipment::class): OrderInterface
    {
        [$items, $parentLogisticItems] = $this->initLogisticItems($syliusOrder);
        Assert::notEmpty($items->toArray());

        $class = new $className($syliusOrder, $items);
        Assert::isInstanceOf($class, OrderInterface::class);

        return $class;
    }

    public function createNewOrderStock(BaseOrder $syliusOrder, string $className = OrderStock::class): OrderInterface
    {
        $shippingAddress = $this->addressFactory->createNew($syliusOrder->getShippingAddress(), $syliusOrder->getCustomer());
        [$items, $parentLogisticItems] = $this->initLogisticItems($syliusOrder);

        $deliveryDate = null;
        if ($syliusOrder->getShipments()->last()) {
            $deliveryDate = $this->deliveryDateFactory->createNew($syliusOrder->getShipments()->last());
        }

        $parentOrder = null !== $syliusOrder->getParentNumber() ?
            $this->orderRepository->findOneByNumber($syliusOrder->getParentNumber()) :
            null;

        $class = new $className(
            $syliusOrder,
            $items,
            $shippingAddress,
            null,
            $deliveryDate,
            null,
            null,
            null,
            $parentOrder
        );
        Assert::isInstanceOf($class, OrderInterface::class);

        return $class;
    }

    public function createNewOrderReturn(BaseOrder $syliusOrder, string $className = OrderReturn::class): OrderInterface
    {
        /**
         * @var OrderReturn $order
         */
        $order = $this->createNew($syliusOrder, $className);
        $parentOrder = $this->orderRepository->findInitialOrder($syliusOrder->getNumber());

        $variantCombo = $syliusOrder->getItemsCombo();

        [$items, $parentLogisticItems] = $this->initLogisticItems($syliusOrder, $parentOrder);
        // Remove item from array if not in return
        /**
         * @var Item $item
         */
        foreach ($items as $key => $item) {
            $sku = $item->sku;
            if (str_starts_with((string) $item->sku, 'R')) {
                $sku = substr((string) $item->sku, 1);
            }
            $parentItem = !empty($parentLogisticItems[$sku]) ? array_shift($parentLogisticItems[$sku]) : $item->sku;
            $item->skuSale = empty($item->skuSale) ? $item->skuSale : $parentItem;
            $skuData = [
                $item->skuSale,
                $item->sku,
                'R' . $item->sku,
                'R' . $item->skuSale,
            ];

            $orderItems = $syliusOrder->getItems()->filter(
                function (OrderItem $orderItem) use ($skuData, $variantCombo): bool {
                    if ($orderItem->getId()) {
                        foreach ($skuData as $row) {
                            if ($row === $orderItem->getVariant()->getCode() || str_contains(needle: $row, haystack: (string) $orderItem->getVariant()->getCode()) || in_array($row, $variantCombo)) {
                                return true;
                            }
                        }
                    }

                    return false;
                }
            );

            if ($orderItems->isEmpty()) {
                unset($items[$key]);
            }
        }

        $order->items = $items;

        return $order;
    }

    protected function initItems(BaseOrder $syliusOrder): ArrayCollection
    {
        $items = new ArrayCollection();
        /**
         * @var OrderItemInterface $item
         */
        foreach ($syliusOrder->getItems() as $item) {
            /**
             * @var ProductVariant $variant
             */
            $variant = $item->getVariant();
            if (null !== $variant && $variant->isBundle()) {
                foreach ($variant->getBundleAssociations() as $bundleAssociation) {
                    $orderItem = new OrderItem();
                    $orderItem->setOrder($syliusOrder);
                    $orderItem->setVariant($bundleAssociation->getVariant());
                    $orderItem->setVariantName($bundleAssociation->getVariant()->getName() . ' [BUNDLE]');
                    $orderItem->setUnitPrice($bundleAssociation->getPrice());
                    for ($i = 0; $i < $bundleAssociation->getQuantity(); ++$i) {
                        new OrderItemUnit($orderItem);
                    }

                    $items->add($this->itemFactory->createNew($orderItem));
                }
            } else {
                $items->add($this->itemFactory->createNew($item));
            }
        }

        // Handle paid shipment
        if (false === $syliusOrder->getShipments()->isEmpty() && $syliusOrder->getShippingTotal() > 0) {
            $items->add($this->shipmentItemFactory->createNew($syliusOrder));
        }

        return $items;
    }

    protected function initLogisticItems(BaseOrder $syliusOrder, ?BaseOrder $parentOrder = null): array
    {
        $items = new ArrayCollection();

        /* @var OrderItem $item */
        [$logisticItems, $parentLogisticItems] = $this->logisticItems->getLogisticItemsComboFromOrder($syliusOrder, $this->productVariantRepository, $this->productComboRepository, $parentOrder);

        foreach ($logisticItems as $item) {
            $items->add($this->itemFactory->createNew($item));
        }

        Assert::notEmpty($items->toArray());

        return [$items, $parentLogisticItems];
    }
}
