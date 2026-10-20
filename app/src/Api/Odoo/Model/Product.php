<?php

namespace App\Api\Odoo\Model;

use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Info(title="Odoo api", version="0.1")
 */
class Product
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
    public $name;

    /**
     * @var string
     *
     * @OA\Property
     */
    public $slug;

    /**
     * @var string
     *
     * @OA\Property
     */
    public $taxonomy;

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
        $this->name = $request->request->get('name');
        $this->slug = $request->request->get('slug');
        $this->taxonomy = $request->request->get('taxonomy');
        $this->options = $request->request->get('options');
    }
}
