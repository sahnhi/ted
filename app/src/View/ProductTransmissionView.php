<?php
/**
 * Created by PhpStorm.
 * User: florent
 * Date: 19/03/19
 * Time: 16:11.
 */

namespace App\View;

class ProductTransmissionView
{
    /**
     * @var array|ProductVariantTransmissionView[]
     */
    public $variants = [];

    public function getWeight(): float
    {
        return round($this->variants[0]->weight, 2);
    }

    /**
     * @return float|int
     */
    public function getVolume()
    {
        return $this->variants[0]->height * $this->variants[0]->depth * $this->variants[0]->width;
    }
}
