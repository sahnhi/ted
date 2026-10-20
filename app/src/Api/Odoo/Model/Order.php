<?php

namespace App\Api\Odoo\Model;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Services\Channel\ChannelCode;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Setono\SyliusGiftCardPlugin\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use Webmozart\Assert\Assert;

class Order extends AbstractOrder implements OrderInterface
{
    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("payment_ref")
     */
    public $paymentId;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("payment_code")
     */
    public $paymentMethod;

    /**
     * @var ArrayCollection<Promotion>
     *
     * @Serializer\Type("ArrayCollection<App\Api\Odoo\Model\Promotion>")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     */
    public $promotions;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("sale_amount_with_tax")
     */
    public $totalTTC;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("sale_amount_without_tax")
     */
    public $totalHT;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("tax_amount")
     */
    public $totalTaxes;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("global_promotions_amount")
     */
    public $totalPromotions = 0;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("amountgiftcardused")
     */
    public $totalGiftCard = 0;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("tax_code_promo_giftcard")
     */
    public $giftCardTaxCode;

    public function __construct(
        BaseOrderInterface $order,
        ArrayCollection $items,
        ?AddressInterface $shippingAddress = null,
        ?AddressInterface $invoicingAddress = null,
        ?DeliveryDateInterface $deliveryDate = null,
        ?CustomerInterface $customer = null,
        ?string $paymentId = null,
        ?string $taxRateCode = null
    ) {
        parent::__construct($order, $items, $shippingAddress, $invoicingAddress, $deliveryDate, $customer);

        /* @var BaseOrder $order */
        $this->promotions = new ArrayCollection();

        $this->store = (null !== $order->getOrderStore() && null !== $order->getOrderStore()->getStore()) ?
            $order->getOrderStore()->getStore()->getName() :
            null;

        $payment = $order->getHighestPayment();

        if (null !== $payment && ChannelCode::CHANNEL_RETURN !== $order->getChannel()->getCode()) {
            $this->paymentId = $paymentId;

            Assert::notNull($payment->getMethod());
            $this->paymentMethod = $payment->getMethod()->getCode();
        } elseif (null !== $order->getId()) {
            $this->paymentMethod = self::PAYMENT_CODE_FREE;
        }

        foreach ($order->getPromotions() as $promotion) {
            $this->promotions->add(new Promotion($promotion->getCode()));
        }

        $this->totalPromotions = $order->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT);
        $this->giftCardTaxCode = $taxRateCode;
        if ($order->hasGiftCards()) {
            $this->totalGiftCard = $order->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_GIFT_CARD_ADJUSTMENT);
        }

        $this->totalTTC = $order->getTotal();
        $this->totalTaxes = $order->getTaxTotal();
        $this->totalHT = ($order->getTotal() + abs($this->totalGiftCard)) - $order->getTaxTotal();
    }
}
