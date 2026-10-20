<?php

namespace App\View;

class OrderReasonView
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $createdAt;

    /**
     * @var string
     */
    public $createdBy;

    /**
     * @var string
     */
    public $group;

    /**
     * @var string
     */
    public $variant;

    /**
     * @var string
     */
    public $quantity;

    /**
     * @var string
     */
    public $comment;

    /**
     * @var string
     */
    public $commercialOffer;

    public function formatToString(): string
    {
        return
            $this->group . ' ' .
            $this->name . ' ' .
            $this->variant . ' (' .
            $this->quantity . ') ' .
            $this->commercialOffer . ' - created at ' .
            $this->createdAt . ' By ' .
            $this->createdBy
        ;
    }
}
