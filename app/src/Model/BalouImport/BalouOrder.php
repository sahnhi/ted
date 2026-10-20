<?php

namespace App\Model\BalouImport;

use App\Doctrine\Entity\Sylius\Order;

class BalouOrder
{
    /**
     * @var string
     */
    public $number;

    /**
     * @var \DateTime
     */
    public $checkoutDate;

    /**
     * @var string
     */
    public $tracking;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $note;

    /**
     * @var Order
     */
    public $parent;

    /**
     * @var int
     */
    public $bisReturn = 0;
}
