<?php

namespace App\Api\Odoo\Model;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Doctrine\Entity\OrderItemProductCombo;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Services\Channel\ChannelCode;
use JMS\Serializer\Annotation as Serializer;
use Setono\SyliusGiftCardPlugin\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\TaxRateInterface;
use Webmozart\Assert\Assert;

class Item implements ItemInterface
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    public $sku;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("sku_sale_line")
     */
    public $skuSale;

    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     */
    public $label;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     */
    public $qty;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("unit_price_with_tax")
     */
    public $priceTTC;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("discounted_unit_price_with_tax")
     */
    public $discountedPriceTTC;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("tax_code")
     */
    public $vat;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("ecotax_amount")
     */
    public $ecotax;

    /**
     * @var int
     */
    public $taxes;

    /**
     * @var bool
     *
     * @Serializer\Exclude
     *
     * @Serializer\Type("boolean")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("to_scrap")
     */
    public $toScrap;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_RETURN})
     *
     * @Serializer\SerializedName("return_reason")
     */
    public $returnReason;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_SHIPPED})
     *
     * @Serializer\SerializedName("lot")
     */
    public $lot = '';

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("total_price_without_tax")
     */
    public $totalHT;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     *
     * @Serializer\SerializedName("total_price_with_tax")
     */
    public $totalTTC;

    public function __construct(OrderItemInterface $item, ?TaxRateInterface $taxRate = null)
    {
        $taxRate ??= $item->getVariant()->getTaxCategory()->getRates()->first();

        /* @var OrderItem $item */
        $this->sku = $item->getVariant()->getCode();
        $this->label = $item->getVariantName();
        $this->qty = $item->getQuantity();
        if (0 === $item->getUnitPrice()) {
            $variant = $item->getVariant();
            foreach ($variant->getChannelPricings() as $channelPricing) {
                if (ChannelCode::CHANNEL_WEB === $channelPricing->getChannelCode()) {
                    $this->priceTTC = $channelPricing->getPrice();
                }
            }
        } elseif (str_contains((string) $item->getVariantName(), 'BUNDLE')) {
            $order = $item->getOrder();
            $pricing = $item->getVariant()->getChannelPricings()->get($order->getChannel()->getCode());
            $this->priceTTC = $pricing->getPrice();
        } else {
            $this->priceTTC = $item->getUnitPrice();
        }
        $this->discountedPriceTTC = $item->getDiscountedUnitPrice();
        $this->vat = $taxRate ? $taxRate->getCode() : $item->getVariant()->getTaxCategory()->getCode();
        /** @var ProductVariantInterface $variant */
        $variant = $item->getVariant();
        $this->ecotax = $variant->getEcotax();
        $this->toScrap = str_contains((string) $this->sku, 'R');

        // We need to remove Order based adjustments promotion from item
        $totalOrderPromotion = abs($item->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT));
        $this->totalTTC = $item->getTotal() + $totalOrderPromotion;

        // Get TVA without Order based adjustments promotion
        $this->taxes = $item->getTaxTotal() * $this->totalTTC / ($item->getTotal() ?: 1);

        $tva = 1;
        $tva += ($taxRate && !empty($taxRate->getAmount())) ? $taxRate->getAmount() : 0;

        $this->totalHT = $this->totalTTC / $tva;

        if ($item->getOrderItemLogistics()->count() > 0) {
            $this->lot = $item->getOrderItemLogistics()->first()->getBatchNumber();
        }

        $reason = $item->getOrderReason();
        if (null !== $reason) {
            Assert::notNull($reason->getReason());
            $this->returnReason = $reason->getReason()->getName();
        }

        /* @var OrderItemProductCombo $combo */
        $this->skuSale = ($combo = $item->getOrderItemProductCombo()->first()) ?
            $combo->getProductCombo()->getProductVariant()->getCode() :
            $item->getVariant()->getCode();
    }
}
