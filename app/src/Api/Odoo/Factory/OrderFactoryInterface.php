<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\Order;
use App\Api\Odoo\Model\OrderInterface;
use App\Api\Odoo\Model\OrderReturn;
use App\Api\Odoo\Model\OrderShipment;
use App\Api\Odoo\Model\OrderStock;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Provider\TaxRateProviderInterface;
use App\Services\Payment\TransactionIdFinder;
use App\Services\Shipment\LogisticItemsInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface OrderFactoryInterface
{
    public const DEFAULT_FUNCTION = 'createNew';

    public function __construct(
        CustomerFactoryInterface $customerFactory,
        AddressFactoryInterface $addressFactory,
        DeliveryDateFactoryInterface $deliveryDateFactory,
        ItemFactoryInterface $itemFactory,
        TransactionIdFinder $transactionIdFinder,
        LogisticItemsInterface $logisticItems,
        OrderRepositoryInterface $orderRepository,
        TaxRateProviderInterface $taxRateProvider,
        RepositoryInterface $productVariantRepository,
        RepositoryInterface $productComboRepository,
        ShipmentItemFactoryInterface $shipmentItemFactory,
    );

    public function createNew(BaseOrder $syliusOrder, string $className = Order::class): OrderInterface;

    public function createNewOrderShipment(BaseOrder $syliusOrder, string $className = OrderShipment::class): OrderInterface;

    public function createNewOrderStock(BaseOrder $syliusOrder, string $className = OrderStock::class): OrderInterface;

    public function createNewOrderReturn(BaseOrder $syliusOrder, string $className = OrderReturn::class): OrderInterface;
}
