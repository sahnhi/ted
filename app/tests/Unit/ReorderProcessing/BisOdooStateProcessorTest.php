<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Api\Odoo\Model\OrderInterface as OdooOrder;
use App\Doctrine\Entity\Sylius\Order;
use App\ReorderProcessing\BisOdooStateProcessor;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;

class BisOdooStateProcessorTest extends TestCase
{
    /** @var BisOdooStateProcessor */
    private $processor;

    /** @var OrderInterface */
    private $order;

    /** @var OrderInterface */
    private $reorder;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
    }

    public function setUpProcessor()
    {
        $this->processor = new BisOdooStateProcessor();
    }

    public function testProcess(): void
    {
        $this->setUpProcessor();
        $this->processor->process($this->order, $this->reorder);

        $this->assertEquals(OdooOrder::ODOO_STATE_CREATED, $this->reorder->getOdooState());
    }
}
