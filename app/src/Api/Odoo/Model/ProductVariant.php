<?php

namespace App\Api\Odoo\Model;

use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Info(title="Odoo api", version="0.1")
 */
class ProductVariant
{
    /**
     * @var string
     *
     * @OA\Property
     */
    public $code;

    /**
     * @var string
     *
     * @OA\Property
     */
    public $sku;

    /**
     * @var string
     *
     * @OA\Property
     */
    public $name;

    /**
     * @var float
     *
     * @OA\Property
     */
    public $weight;

    /**
     * @var float
     *
     * @OA\Property
     */
    public $length;

    /**
     * @var float
     *
     * @OA\Property
     */
    public $width;

    /**
     * @var float
     *
     * @OA\Property
     */
    public $height;

    /**
     * @var bool
     *
     * @OA\Property
     */
    public $tracked;

    /**
     * @var bool
     *
     * @OA\Property
     */
    public $shippingRequired;

    /**
     * @var string
     *
     * @OA\Property
     */
    public $deliveryCategory;

    /**
     * @var int
     *
     * @OA\Property
     */
    public $salePrice;

    /**
     * @OA\Property
     */
    public $ecotax;

    /**
     * @OA\Property
     */
    public $billOfMaterial;

    /**
     * @OA\Property
     */
    public $options;

    /**
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->code = $request->request->get('code');
        $this->sku = $request->request->get('sku');
        $this->name = $request->request->get('name');
        $this->weight = $request->request->get('weight');
        $this->length = $request->request->get('length');
        $this->width = $request->request->get('width');
        $this->height = $request->request->get('height');
        $this->tracked = $request->request->get('tracked');
        $this->shippingRequired = $request->request->get('shipping_required');
        $this->deliveryCategory = $request->request->get('delivery_category');
        $this->salePrice = $request->request->get('sale_price');
        $this->ecotax = $request->request->get('ecotax');
        $this->billOfMaterial = $request->request->get('bill_of_material');
        $this->options = $request->request->get('options');
    }
}
