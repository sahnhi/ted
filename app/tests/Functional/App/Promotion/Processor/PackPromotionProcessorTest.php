<?php

declare(strict_types=1);

namespace App\Tests\Functional\App\Promotion\Processor;

use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Doctrine\Repository\Sylius\PromotionCouponRepository;
use App\Factory\OrderFactory;
use App\Tests\Functional\WebTestCase;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;

class PackPromotionProcessorTest extends WebTestCase
{
    public function testItAppliesTheMostAdvantageousPromotionOnAOrderItemWithAPack(): void
    {
        /** @var OrderFactory $orderFactory */
        $orderFactory = $this->getContainer()->get('sylius.factory.order');
        $order = $orderFactory->createNew();
        $channel = $this->getContainer()->get('sylius.repository.channel')->findOneByCode('default');
        $order->setChannel($channel);
        $order->setCurrencyCode('EUR');
        $order->setTokenValue('5811cf75-84a3-4673-9cc3-00755d1b72az');
        $order->setLocaleCode('fr_FR');

        /** @var ProductVariantRepositoryInterface $productVariantRepo */
        $productVariantRepo = $this->getContainer()->get('sylius.repository.product_variant');
        $packLitVariant = $productVariantRepo->findOneByCode('PACK_LIT_ADULTE');
        $packCouetteVariant = $productVariantRepo->findOneByCode('PACK_COUETTE');
        $mattressVariant = $productVariantRepo->findOneByCode('TED140190');

        /** @var CartItemFactoryInterface $orderItemFactory */
        $orderItemFactory = $this->getContainer()->get('sylius.custom_factory.order_item');
        /** @var OrderItemQuantityModifierInterface $orderItemQuantityModifier */
        $orderItemQuantityModifier = $this->getContainer()->get('sylius.order_item_quantity_modifier');

        $packLitItem = $orderItemFactory->createForCart($order);
        $packLitItem->setVariant($packLitVariant);
        $orderItemQuantityModifier->modify($packLitItem, 1);

        $packCouetteItem = $orderItemFactory->createForCart($order);
        $packCouetteItem->setVariant($packCouetteVariant);
        $orderItemQuantityModifier->modify($packCouetteItem, 1);

        $mattressItem = $orderItemFactory->createForCart($order);
        $mattressItem->setVariant($mattressVariant);
        $orderItemQuantityModifier->modify($mattressItem, 1);

        $coupon = $this->getContainer()->get(PromotionCouponRepository::class)->findOneByCodeAndPromotionCode('promotion_15', '15off');
        $order->setPromotionCoupon($coupon);

        $this->getContainer()->get('sylius.order_processing.order_processor')->process($order);

        foreach ($order->getItems() as $orderItem) {
            switch ($orderItem->getVariant()->getCode()) {
                case 'TED140190':
                    self::assertEquals($mattressVariant->getChannelPricingForChannel($channel)->getPrice() * 0.85, $orderItem->getTotal());
                    break;
                case 'PACK_LIT_ADULTE':
                    self::assertEquals($packLitVariant->getChannelPricingForChannel($channel)->getPrice() * 0.85, $orderItem->getTotal());
                    break;
                case 'PACK_COUETTE':
                    self::assertEquals($packCouetteVariant->getChannelPricingForChannel($channel)->getPrice() * 0.80, $orderItem->getTotal());
                    break;
            }
        }
        self::assertCount(3, $order->getItems());
    }
}
