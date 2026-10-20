<?php

namespace App\Tests\Unit\Api\Odoo\Factory;

use App\Api\Odoo\Factory\ItemFactory;
use App\Api\Odoo\Model\ItemInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Provider\TaxRateProviderInterface;
use App\Tests\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Taxation\Model\TaxCategory;

class ItemFactoryTest extends TestCase
{
    /** @var ItemFactory */
    private $factory;

    /** @var OrderItemInterface */
    private $item;

    /** @var TaxRateProviderInterface */
    private $taxRateProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxRateProvider = $this->createMock(TaxRateProviderInterface::class);
    }

    public function setUpFactory()
    {
        $this->factory = new ItemFactory($this->taxRateProvider);
    }

    private function setUpCreateNew()
    {
        $variant = $this->createMock(ProductVariantInterface::class);
        $variant
            ->method('getName')
            ->willReturn('name');
        $variant
            ->method('getTaxCategory')
            ->willReturn(new TaxCategory());
        $variant
            ->method('getCode')
            ->willReturn('code');
        $variant
            ->method('getChannelPricings')
            ->willReturn(new ArrayCollection());

        $this->item = new OrderItem();
        $this->item->setVariant($variant);
        $this->item->setOrder(new Order());

        $this->setUpFactory();
    }

    public function testCreateNew(): void
    {
        $this->setUpCreateNew();
        $item = $this->factory->createNew($this->item);

        $this->assertInstanceOf(ItemInterface::class, $item);
    }
}
