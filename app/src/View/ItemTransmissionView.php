<?php

namespace App\View;

use App\Doctrine\Entity\Sylius\Product;

class ItemTransmissionView
{
    public $id;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var int
     */
    public $total;

    /**
     * @var string
     */
    public $itemCode;

    /**
     * @var string
     */
    public $productCode;

    /**
     * @var ProductTransmissionView
     */
    public $product;

    public Product $parentProduct;

    public function __construct()
    {
        $this->product = new ProductTransmissionView();
    }

    /**
     * @return int
     */
    public function getQteAExpedier()
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->product->variants[0]->code;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->product->variants[0]->name;
    }

    public function getProduit(): string
    {
        return $this->quantity . ' x ' . $this->getDescription();
    }

    /**
     * @return float|int
     */
    public function getWeights()
    {
        return $this->product->getWeight();
    }

    /**
     * @return float|int
     */
    public function getVolumes()
    {
        return $this->product->getVolume() / 1000000;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->product->variants[0]->code;
    }
}
