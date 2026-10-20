<?php

namespace App\Api\Odoo\Model;

use JMS\Serializer\Annotation as Serializer;

class DeliveryDate implements DeliveryDateInterface
{
    /**
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\SerializedName("start")
     */
    public $startDate;

    /**
     * @Serializer\Type("DateTime<'c'>")
     *
     * @Serializer\SerializedName("end")
     */
    public $endDate;

    public function __construct(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}
