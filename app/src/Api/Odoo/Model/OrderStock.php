<?php

namespace App\Api\Odoo\Model;

use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Model\OrderInterface;

class OrderStock extends AbstractOrder
{
    public function __construct(
        BaseOrderInterface $order,
        ArrayCollection $items,
        ?AddressInterface $shippingAddress = null,
        ?AddressInterface $invoicingAddress = null,
        ?DeliveryDateInterface $deliveryDate = null,
        ?CustomerInterface $customer = null,
        private readonly ?string $paymentId = null,
        private readonly ?string $taxRateCode = null,
        ?BaseOrderInterface $parent = null
    ) {
        /* @var BaseOrder $order */
        parent::__construct($order, $items, $shippingAddress, $invoicingAddress, $deliveryDate, $customer);

        $this->suffix = $order->getBis() > 0 ? 'B' . $order->getBis() : '';

        $this->store = (null !== $order->getOrderStore() && null !== $order->getOrderStore()->getStore()) ?
            $order->getOrderStore()->getStore()->getName() :
            null;

        if (null !== $parent && OrderInterface::STATE_CANCELLED === $parent->getState()) {
            $this->parentPickingCancel = $parent->getNumber();
        }
    }
}
