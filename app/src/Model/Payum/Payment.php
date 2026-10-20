<?php

namespace App\Model\Payum;

use Payum\Core\Model\Payment as BasePayment;

class Payment extends BasePayment
{
    /**
     * @var array
     */
    protected $order;

    public function __construct()
    {
        $this->order = [];
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param array|\Traversable $order
     */
    public function setOrder($order)
    {
        if ($order instanceof \Traversable) {
            $order = iterator_to_array($order);
        }

        $this->order = $order;
    }
}
