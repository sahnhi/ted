<?php

namespace App\Api\Odoo\Model;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Api\Odoo\Model\OrderInterface as ModelOrderInterface;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;

class OrderReturn extends AbstractOrder
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("return_code")
     */
    public $returnCode;

    public function __construct(
        BaseOrderInterface $order,
        ArrayCollection $items,
        ?AddressInterface $shippingAddress = null,
        ?AddressInterface $invoicingAddress = null,
        ?DeliveryDateInterface $deliveryDate = null,
        ?CustomerInterface $customer = null,
        private readonly ?string $paymentId = null,
        private readonly ?string $taxRateCode = null
    ) {
        /* @var BaseOrder $order */
        parent::__construct($order, $items, $shippingAddress, $invoicingAddress, $deliveryDate, $customer);

        $matches = [];
        preg_match("/([0-9]+)(\d{3})/", $this->name, $matches);

        $base = !empty($matches) ? $matches[0] : $this->name;
        $this->suffix = substr($this->name, strpos($this->name, $base) + strlen($base));

        $this->setReturnCode($order);
        $this->store = '';

        if (null === $this->shippingDate) {
            $this->shippingDate = new \DateTime();
        }
    }

    private function setReturnCode(BaseOrder $order)
    {
        $this->returnCode = str_replace('BR', 'R', (string) preg_replace('/[0-9]+/', '', $this->suffix));
        foreach ($order->getOrderReason() as $orderReason) {
            if (ModelOrderInterface::ODOO_RETURN_CODE_RGC_ID === $orderReason->getReason()->getGroupName()->getId()) {
                $this->returnCode = ModelOrderInterface::ODOO_RETURN_CODE_RGC;
            }
            if (ModelOrderInterface::ODOO_RETURN_CODE_RR_ID === $orderReason->getReason()->getGroupName()->getId()) {
                $this->returnCode = ModelOrderInterface::ODOO_RETURN_CODE_RR;
            }
        }
    }
}
