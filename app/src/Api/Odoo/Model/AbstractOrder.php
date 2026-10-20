<?php

namespace App\Api\Odoo\Model;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Services\Channel\ChannelCode;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;

abstract class AbstractOrder implements OrderInterface
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    public $name;

    /**
     * @var string
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_STOCK, OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\Type("string")
     */
    public $suffix;

    /**
     * @var Customer
     *
     * @Serializer\Type("App\Api\Odoo\Model\Customer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_RETURN})
     */
    public $customer;

    /**
     * @var ShippingAddress
     *
     * @Serializer\Type("App\Api\Odoo\Model\ShippingAddress")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_STOCK, OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("shipping_address")
     */
    public $shippingAddress;

    /**
     * @var BillingAddress
     *
     * @Serializer\Type("App\Api\Odoo\Model\BillingAddress")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("invoicing_address")
     */
    public $invoicingAddress;

    /**
     * @var \DateTime
     *
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("confirmation_date")
     */
    public $confirmationDate;

    /**
     * @var \DateTime
     *
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_STOCK, OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("shipping_date")
     */
    public $shippingDate;

    /**
     * @var DeliveryDate
     *
     * @Serializer\Type("App\Api\Odoo\Model\DeliveryDate")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_STOCK, OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("delivery_slot")
     */
    public $deliveryDate;

    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_STOCK, OdooApiInterface::API_CALL_RETURN})
     */
    public $salesman;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_STOCK, OdooApiInterface::API_CALL_RETURN})
     */
    public $channel;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_STOCK, OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("delivery_mode")
     */
    public $deliveryMethod;

    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_RETURN, OdooApiInterface::API_CALL_STOCK})
     */
    public $store;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER, OdooApiInterface::API_CALL_STOCK, OdooApiInterface::API_CALL_RETURN})
     */
    public $warehouse = '';

    /**
     * @var ArrayCollection<OrderItem>
     *
     * @Serializer\Type("ArrayCollection<App\Api\Odoo\Model\OrderItem>")
     *
     * @Serializer\SerializedName("lines")
     */
    public $items;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_STOCK})
     *
     * @Serializer\SerializedName("parent_picking_cancel")
     */
    public $parentPickingCancel = '';

    public function __construct(
        BaseOrderInterface $order,
        ArrayCollection $items,
        ?AddressInterface $shippingAddress = null,
        ?AddressInterface $invoicingAddress = null,
        ?DeliveryDateInterface $deliveryDate = null,
        ?Customer $customer = null,
        private readonly ?string $paymentId = null,
        private readonly ?string $taxRateCode = null,
    ) {
        /**
         * @var BaseOrder $order
         */
        $store = $order->getOrderStore();
        $seller = null !== $store ? $store->getSeller() : null;

        $this->customer = $customer;
        $this->shippingAddress = $shippingAddress;
        $this->invoicingAddress = $invoicingAddress;
        $this->confirmationDate = $order->getCheckoutCompletedAt();

        $this->deliveryDate = $deliveryDate;

        $this->name = $order->getNumber();
        $this->salesman = null !== $seller ? $seller->getName() : null;
        if (
            !empty($order->getOrderStore())
            && !empty($order->getOrderStore()->getStore())
            && !empty($order->getOrderStore()->getStore()->getChannel())
            && ChannelCode::CHANNEL_RETAIL === $order->getOrderStore()->getStore()->getChannel()->getCode()
        ) {
            $this->channel = ChannelCode::CHANNEL_RETAIL;
        } else {
            $this->channel = ChannelCode::CHANNEL_WEB === $order->getChannel()->getCode() ? 'web' : $order->getChannel()->getCode();
        }

        /**
         * @var Shipment $shipment
         */
        $shipment = $order->getShipments()->last();

        if ($shipment) {
            $this->deliveryMethod = $shipment->getMethod()->getCode();
            // this is the date in which the products are out from the stock in the warehouse
            $this->shippingDate = $shipment->getShippedAt() ?? $shipment->getStartDeliverySlot();

            /**
             * @var ShippingMethod $shippingMethod
             */
            $shippingMethod = $shipment->getMethod();
            if (ChannelCode::CHANNEL_RETAIL === $order->getChannel()->getCode()) {
                $this->warehouse = (null !== $order->getOrderStore() && null !== $order->getOrderStore()->getStore()) ?
                    $order->getOrderStore()->getStore()->getName() :
                    '';
            } elseif (null !== $shippingMethod->getWarehouse() && null !== $shippingMethod->getWarehouse()->getCrmName()) {
                $this->warehouse = $shippingMethod->getWarehouse()->getCrmName();
            } else {
                $this->warehouse = null !== $shippingMethod->getWarehouse() ? strtolower((string) $shippingMethod->getWarehouse()->getName()) : '';
            }
        } else {
            // in case of buying a virtual product, like promotional coupon which will not transit through the warehouse
            $this->shippingDate = $order->getCheckoutCompletedAt();
        }

        if (ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode() && '' === $this->warehouse) {
            switch ($order->getReturnType()) {
                case Reason::RETURN_TYPE_WAREHOUSE:
                case Reason::RETURN_TYPE_SENDER:
                    $this->warehouse = 'Quai77';
                    break;
                default:
                    break;
            }
        }

        $isAGiftCard = $order->getItemUnits()->exists(
            fn ($key, OrderItemUnit $item): bool => null !== $item->getGiftCard());

        if ($isAGiftCard) {
            $this->warehouse = 'Quai77';
            if ($customer instanceof Customer) {
                $this->customer->firstname = 'Tediber';
                $this->customer->lastname = 'CarteCadeau';
                $this->shippingAddress = new ShippingAddress($order->getBillingAddress(), $order->getCustomer());
            }
        }

        if (ChannelCode::CHANNEL_RETAIL === $order->getChannel()->getCode()) {
            $this->extendeRetailOrder($order);
        }

        $this->items = $items;
    }

    /**
     * Some special rules must be applied to retail orders:
     * - The warehouse is the same as the store name
     * - The delivery date is the checkout completed date (we consider all retail orders as delivered immediately, this is an assomption)
     * - The shipping date is the checkout completed date (we consider all retail orders as shipped immediately, this is an assomption)
     * Even if the rules above mey seem weird, they have been decided with the PO and the business, following the constraints
     * of the the retail tool and date written on the DB
     */
    private function extendeRetailOrder(BaseOrderInterface $order): void
    {
        if (ChannelCode::CHANNEL_RETAIL !== $order->getChannel()->getCode()) {
            return;
        }

        // fallback in case of missing warehouse for a retail order
        /**
         * @var BaseOrder $order
         */
        $storeName = $order->getOrderStore()?->getStore()?->getName();
        if (empty($this->warehouse) && !is_null($storeName)) {
            $this->warehouse = $storeName;
        }

        // An retail order is considered as delivered immediately after the payment even if the custoemr choosed
        // to pick up later, or send using a delivery method. This has been decided with the PO and the business
        $this->deliveryDate = new DeliveryDate($order->getCheckoutCompletedAt(), $order->getCheckoutCompletedAt());
        $this->shippingDate = \DateTime::createFromInterface($order->getCheckoutCompletedAt());
    }
}
