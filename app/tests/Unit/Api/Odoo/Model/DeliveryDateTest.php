<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\DeliveryDate;
use App\Tests\TestCase;

class DeliveryDateTest extends TestCase
{
    /** @var DeliveryDate */
    private $deliveryDate;

    public function setUpModel(\DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        $this->deliveryDate = new DeliveryDate($startDate, $endDate);
    }

    public function testConstruct(): void
    {
        $startDate = new \DateTime();
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('PT1H'));

        $this->setUpModel($startDate, $endDate);
        $this->assertEquals($startDate, $this->deliveryDate->startDate);
        $this->assertEquals($endDate, $this->deliveryDate->endDate);
    }
}
