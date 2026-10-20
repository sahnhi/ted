<?php

declare(strict_types=1);

namespace App\Tests\Functional\Modifier\Shipping;

use App\Doctrine\Entity\ShippingMethodCategoryService;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Enum\AdjustmentTypeEnum;
use App\Modifier\Shipping\ShippingMethodCategoryServiceOrderModifier;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelPricing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ShippingMethodCategoryServiceOrderModifierTest extends KernelTestCase
{
    private readonly ShippingMethodCategoryServiceOrderModifier $modifier;

    private readonly OrderRepository $orderRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->modifier = self::$container->get(ShippingMethodCategoryServiceOrderModifier::class);
        $this->orderRepository = self::$container->get('sylius.repository.order');
    }

    public function testItDoesNotAddServicesToAnEmptyOrder(): void
    {
        /** @var ChannelRepositoryInterface $channelRepository */
        $channelRepository = self::$container->get('sylius.repository.channel');

        /** @var Channel $channel */
        $channel = $channelRepository->findOneByCode('default');

        // A new order has no items
        $order = new Order();
        $order->setChannel($channel);
        $order->setCurrencyCode('EUR');

        $includedServiceProduct = new Product();
        $includedServiceVariant = new ProductVariant();
        $includedServiceVariant->setProduct($includedServiceProduct);

        $includedVariantChannelPricing = new ChannelPricing();
        $includedVariantChannelPricing->setChannelCode($channel->getCode());
        $includedVariantChannelPricing->setProductVariant($includedServiceVariant);
        $includedVariantChannelPricing->setPrice(1000);

        $includedServiceVariant->addChannelPricing($includedVariantChannelPricing);

        $includedService = new ShippingMethodCategoryService();
        $includedService
            ->setIncluded(true)
            ->setEnabled(true)
            ->setProductVariant($includedServiceVariant);

        $paidServiceProduct = new Product();
        $paidServiceVariant = new ProductVariant();
        $paidServiceVariant->setProduct($paidServiceProduct);

        $paidVariantChannelPricing = new ChannelPricing();
        $paidVariantChannelPricing->setChannelCode($channel->getCode());
        $paidVariantChannelPricing->setProductVariant($paidServiceVariant);
        $paidVariantChannelPricing->setPrice(1500);

        $paidServiceVariant->addChannelPricing($paidVariantChannelPricing);

        $paidService = new ShippingMethodCategoryService();
        $paidService
            ->setIncluded(false)
            ->setEnabled(true)
            ->setProductVariant($paidServiceVariant)
            ->setCode('PAID');

        $this->modifier->addServicesToOrder([$includedService, $paidService], $order);

        self::assertCount(0, $order->getItems());
    }

    public function testItDoesAddServicesToANonEmptyOrder(): array
    {
        /** @var ChannelRepositoryInterface $channelRepository */
        $channelRepository = self::$container->get('sylius.repository.channel');

        /** @var Channel $channel */
        $channel = $channelRepository->findOneByCode('default');

        // This order has a mattress with a quantity of 1
        /** @var Order $order */
        $order = $this->orderRepository->findCartByTokenValue('5811cf75-84a3-4673-9cc3-69755d1b72aa');

        $includedServiceProduct = new Product();
        $includedServiceVariant = new ProductVariant();
        $includedServiceVariant->setProduct($includedServiceProduct);

        $includedVariantChannelPricing = new ChannelPricing();
        $includedVariantChannelPricing->setChannelCode($channel->getCode());
        $includedVariantChannelPricing->setProductVariant($includedServiceVariant);
        $includedVariantChannelPricing->setPrice(1000);

        $includedServiceVariant->addChannelPricing($includedVariantChannelPricing);

        $includedService = new ShippingMethodCategoryService();
        $includedService
            ->setIncluded(true)
            ->setEnabled(true)
            ->setProductVariant($includedServiceVariant);

        $paidServiceProduct = new Product();
        $paidServiceVariant = new ProductVariant();
        $paidServiceVariant->setProduct($paidServiceProduct);

        $paidVariantChannelPricing = new ChannelPricing();
        $paidVariantChannelPricing->setChannelCode($channel->getCode());
        $paidVariantChannelPricing->setProductVariant($paidServiceVariant);
        $paidVariantChannelPricing->setPrice(1500);

        $paidServiceVariant->addChannelPricing($paidVariantChannelPricing);

        $paidService = new ShippingMethodCategoryService();
        $paidService
            ->setIncluded(false)
            ->setEnabled(true)
            ->setProductVariant($paidServiceVariant)
            ->setCode('PAID');

        self::assertCount(1, $order->getItems());
        self::assertSame(6900, $order->getItemsTotal());

        $this->modifier->addServicesToOrder([$includedService, $paidService], $order);

        self::assertCount(3, $order->getItems());

        /** @var OrderItem $includedServiceOrderItem */
        $includedServiceOrderItem = $order->getItems()->get(1);

        self::assertSame($includedServiceVariant, $includedServiceOrderItem->getVariant());
        self::assertSame(0, $includedServiceOrderItem->getTotal());
        self::assertCount(1, $includedServiceOrderItem->getAdjustmentsRecursively(AdjustmentTypeEnum::OFFERED_SERVICE->value));
        self::assertSame(-1000, $includedServiceOrderItem->getAdjustmentsTotalRecursively(AdjustmentTypeEnum::OFFERED_SERVICE->value));

        /** @var OrderItem $paidServiceOrderItem */
        $paidServiceOrderItem = $order->getItems()->get(2);

        self::assertSame($paidServiceVariant, $paidServiceOrderItem->getVariant());
        self::assertSame(1500, $paidServiceOrderItem->getTotal());
        self::assertCount(0, $paidServiceOrderItem->getAdjustmentsRecursively(AdjustmentTypeEnum::OFFERED_SERVICE->value));

        self::assertSame(8400, $order->getItemsTotal());

        return ['order' => $order, 'services' => [$includedService, $paidService]];
    }

    /**
     * @param array{order: Order, services: array<ShippingMethodCategoryService>} $previousTestData
     *
     * @depends testItDoesAddServicesToANonEmptyOrder
     */
    public function testRemoveServicesFromOrder(array $previousTestData): void
    {
        /** @var Order $order */
        $order = $previousTestData['order'];

        self::assertCount(3, $order->getItems());

        /** @var array<ShippingMethodCategoryService> $servicesToRemove */
        $servicesToRemove = $previousTestData['services'];

        $this->modifier->removeServicesFromOrder($servicesToRemove, $order);

        self::assertCount(1, $order->getItems());
        self::assertSame(6900, $order->getItemsTotal());
    }

    public function testAddRestrictedServicesToOrder(): void
    {
        /** @var ChannelRepositoryInterface $channelRepository */
        $channelRepository = self::$container->get('sylius.repository.channel');

        /** @var Channel $channel */
        $channel = $channelRepository->findOneByCode('default');

        // This order has a mattress with a quantity of 1
        /** @var Order $order */
        $order = $this->orderRepository->findCartByTokenValue('5811cf75-84a3-4673-9cc3-69755d1b72aa');

        $restrictedProduct = new Product();
        $restrictedServiceVariant = new ProductVariant();
        $restrictedServiceVariant->setProduct($restrictedProduct);

        $restrictedVariantChannelPricing = new ChannelPricing();
        $restrictedVariantChannelPricing->setChannelCode($channel->getCode());
        $restrictedVariantChannelPricing->setProductVariant($restrictedServiceVariant);
        $restrictedVariantChannelPricing->setPrice(1000);

        $restrictedServiceVariant->addChannelPricing($restrictedVariantChannelPricing);

        $restrictedService = new ShippingMethodCategoryService();
        $restrictedService
            ->setIncluded(true)
            ->setEnabled(true)
            ->setProductVariant($restrictedServiceVariant)
            // This service should be added to the cart ONLY if my cart contains the $restrictedProduct
            // which here, isn't the case
            ->addRestrictedProduct($restrictedProduct);

        $paidServiceProduct = new Product();
        $unRestrictedServiceVariant = new ProductVariant();
        $unRestrictedServiceVariant->setProduct($paidServiceProduct);

        $unRestrictedVariantChannelPricing = new ChannelPricing();
        $unRestrictedVariantChannelPricing->setChannelCode($channel->getCode());
        $unRestrictedVariantChannelPricing->setProductVariant($unRestrictedServiceVariant);
        $unRestrictedVariantChannelPricing->setPrice(1500);

        $unRestrictedServiceVariant->addChannelPricing($unRestrictedVariantChannelPricing);

        $unRestrictedService = new ShippingMethodCategoryService();
        $unRestrictedService
            ->setIncluded(false)
            ->setEnabled(true)
            ->setProductVariant($unRestrictedServiceVariant);

        self::assertCount(1, $order->getItems());
        self::assertSame(6900, $order->getItemsTotal());

        $this->modifier->addServicesToOrder([$restrictedService, $unRestrictedService], $order);

        self::assertCount(2, $order->getItems());

        /** @var OrderItem $unRestrictedServiceOrderItem */
        $unRestrictedServiceOrderItem = $order->getItems()->get(1);

        self::assertSame($unRestrictedServiceVariant, $unRestrictedServiceOrderItem->getVariant());
        self::assertSame(1500, $unRestrictedServiceOrderItem->getTotal());
        self::assertCount(0, $unRestrictedServiceOrderItem->getAdjustmentsRecursively(AdjustmentTypeEnum::OFFERED_SERVICE->value));

        // 8400 = 6900 +
        self::assertSame(8400, $order->getItemsTotal());
    }
}
