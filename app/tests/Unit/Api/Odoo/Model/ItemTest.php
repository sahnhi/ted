<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\Item;
use App\Doctrine\Entity\OrderItemLogistic;
use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Tests\TestCase;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Model\Adjustment;
use Sylius\Component\Taxation\Model\TaxCategory;
use Webmozart\Assert\InvalidArgumentException;

class ItemTest extends TestCase
{
    /** @var Item */
    private $item;

    public function setUpModel(OrderItemInterface $item): void
    {
        $this->item = new Item($item);
    }

    private function setUpItem(?OrderReason $reason = null): OrderItem
    {
        $taxCategory = new TaxCategory();
        $taxCategory->setCode('code');

        $variant = $this->createMock(ProductVariantInterface::class);
        $variant->method('getName')->willReturn('name');
        $variant->method('getTaxCategory')->willReturnCallBack(function () {
            $taxCategory = new TaxCategory();
            $taxCategory->setCode('code');

            return $taxCategory;
        });
        $variant->method('getCode')->willReturn('code');
        $variant->method('getEcotax')->willReturn(20);

        $adjustment = new Adjustment();
        $adjustment->setType(AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT);
        $adjustment->setAmount(20);

        $taxes = new Adjustment();
        $adjustment->setType(AdjustmentInterface::TAX_ADJUSTMENT);
        $adjustment->setAmount(20);

        $item = new OrderItem();
        $item->setUnitPrice(200);
        $item->addAdjustment($adjustment);
        $item->addAdjustment($taxes);
        $item->setVariant($variant);

        new OrderItemUnit($item);

        $item->setOrder($this->createMock(Order::class));

        if (null !== $reason) {
            $item->getOrder()
                ->method('getOrderReasonByVariant')
                ->willReturn($reason);
        }
        $orderItemLogistic = $this->createMock(OrderItemLogistic::class);
        $orderItemLogistic->method('getBatchNumber')->willReturn('test');
        $item->addOrderItemLogistic($orderItemLogistic);

        $this->setUpModel($item);

        return $item;
    }

    public function testConstruct(): void
    {
        $item = $this->setUpItem();
        $this->assertEquals($item->getVariant()->getCode(), $this->item->sku);
        $this->assertEquals($item->getVariantName(), $this->item->label);
        $this->assertEquals($item->getQuantity(), $this->item->qty);
        $this->assertEquals($item->getUnitPrice(), $this->item->priceTTC);
        $this->assertEquals($item->getDiscountedUnitPrice(), $this->item->discountedPriceTTC);
        $this->assertEquals($item->getTaxTotal(), $this->item->taxes);
        $this->assertEquals($item->getVariant()->getTaxCategory()->getCode(), $this->item->vat);
        $this->assertEquals($item->getEcotaxTotal(), $this->item->ecotax);
        $this->assertEquals($item->getTotal(), $this->item->totalTTC);
        $this->assertEquals($item->getTotal(), $this->item->totalHT);
        $this->assertEquals('test', $this->item->lot);
    }

    public function testConstructWithOrderReason(): void
    {
        $reason = new Reason();
        $reason->setName('name');

        $orderReason = new OrderReason();
        $orderReason->setReason($reason);

        $item = $this->setUpItem($orderReason);
        $this->assertEquals($item->getOrderReason()->getReason()->getName(), $this->item->returnReason);
    }

    public function testConstructWithOrderReasonButNoReasonError(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $orderReason = new OrderReason();
        $this->setUpItem($orderReason);
    }
}
