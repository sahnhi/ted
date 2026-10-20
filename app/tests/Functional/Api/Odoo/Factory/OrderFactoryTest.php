<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Odoo\Factory;

use App\Api\Odoo\Factory\OrderFactory;
use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Tests\Functional\WebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Taxation\Model\TaxCategory;

class OrderFactoryTest extends WebTestCase
{
    public function testCreateNew(): void
    {
        $taxCategoryMock = $this->createMock(TaxCategory::class);
        $taxCategoryMock->method('getRates')->willReturn(new ArrayCollection());

        $channelPricingMock = $this->createMock(ChannelPricingInterface::class);
        $channelPricingMock->method('getPrice')->willReturn(150);

        $channelPricingsMock = $this->createMock(ArrayCollection::class);
        $channelPricingsMock->method('get')->willReturn($channelPricingMock);

        $productVariantMock = $this->createMock(ProductVariant::class);
        $productVariantMock->method('getName')->willReturn('name');
        $productVariantMock->method('getCode')->willReturn('code');
        $productVariantMock->method('isBundle')->willReturn(true);
        $productVariantMock->method('getTaxCategory')->willReturn($taxCategoryMock);
        $productVariantMock->method('getChannelPricings')->willReturn(
            $channelPricingsMock
        );

        $bundleAssociationFirstItem = $this->createMock(BundleAssociation::class);
        $bundleAssociationFirstItem->method('getVariant')->willReturn($productVariantMock);
        $bundleAssociationFirstItem->method('getPrice')->willReturn(150);
        $bundleAssociationFirstItem->method('getQuantity')->willReturn(2);
        $productVariantMock->method('getBundleAssociations')->willReturn(
            new ArrayCollection([$bundleAssociationFirstItem])
        );

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getVariant')->willReturn($productVariantMock);

        $customer = $this->createMock(Customer::class);
        $customer->method('getLastName')->willReturn('Doe');
        $customer->method('getFirstName')->willReturn('John');
        $customer->method('getEmail')->willReturn('john.doe@example.com');
        $customer->method('getPhoneNumber')->willReturn('123456789');

        $addressMock = $this->createMock(Address::class);
        $addressMock->method('getCompany')->willReturn('Company Inc.');
        $addressMock->method('getLastName')->willReturn('Doe');
        $addressMock->method('getFirstName')->willReturn('John');
        $addressMock->method('getPhoneNumber')->willReturn('123456789');
        $addressMock->method('getStreet')->willReturn('123 Main St');
        $addressMock->method('getPostcode')->willReturn('12345');
        $addressMock->method('getCountryCode')->willReturn('US');
        $addressMock->method('getCity')->willReturn('Anytown');

        $shippingMethodMock = $this->createMock(ShippingMethod::class);
        $shippingMethodMock->method('getCode')->willReturn('code');

        $shipmentMock = $this->createMock(Shipment::class);
        $shipmentMock->method('getStartDeliverySlot')->willReturn(null);
        $shipmentMock->method('getEndDeliverySlot')->willReturn(null);
        $shipmentMock->method('getMethod')->willReturn($shippingMethodMock);

        $syliusOrder = $this->createMock(Order::class);
        $syliusOrder->method('getItems')->willReturn(new ArrayCollection([$orderItem]));
        $syliusOrder->method('getShippingAddress')->willReturn($addressMock);
        $syliusOrder->method('getChannel')->willReturn($this->createMock(Channel::class));
        $syliusOrder->method('getCustomer')->willReturn($customer);
        $syliusOrder->method('getShipments')->willReturn(
            new ArrayCollection([$shipmentMock])
        );

        $erpOrder = self::$container->get(OrderFactory::class)->createNew($syliusOrder);

        $this->assertNotEmpty($erpOrder->items);
        $this->assertEquals(1, $erpOrder->items->count());
        $this->assertEquals(300, $erpOrder->items->first()->totalTTC);
        $this->assertEquals(2, $erpOrder->items->first()->qty);
    }
}
