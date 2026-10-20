<?php

namespace App\Api\Shipup\DTO;

use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Core\Model\OrderItemInterface;

class ShipupLineItem
{
    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("merchant_id")
     */
    public string $merchantId;

    /**
     * @Serializer\Type("string")
     */
    public string $title;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("variant_title")
     */
    public string $variantTitle;

    /**
     * @Serializer\Type("string")
     */
    public string $name;

    /**
     * @Serializer\Type("string")
     */
    public ?string $description;

    /**
     * @Serializer\Type("integer")
     */
    public int $quantity;

    /**
     * @Serializer\Type("bool")
     *
     * @Serializer\SerializedName("requires_shipping")
     */
    public bool $requiresShipping;

    /**
     * @Serializer\Type("string")
     */
    public string $sku;

    /**
     * @Serializer\Type("bool")
     */
    public bool $canceled = false;

    public function __construct(
        OrderItemInterface $orderItem, /**
     * @Serializer\Type("integer")
     *
     * @Serializer\SerializedName("shipped_quantity")
     */
        public int $shippedQuantity = 0
    ) {
        $this->merchantId = $orderItem->getId();
        $this->title = $orderItem->getProductName();
        $this->variantTitle = $orderItem->getVariantName();
        $this->name = $orderItem->getProductName();
        $this->description = $orderItem->getProduct()->getDescription();
        $this->quantity = $orderItem->getQuantity();
        $this->requiresShipping = $orderItem->getVariant()->isShippingRequired();
        $this->sku = $orderItem->getVariant()->getCode();
    }
}
