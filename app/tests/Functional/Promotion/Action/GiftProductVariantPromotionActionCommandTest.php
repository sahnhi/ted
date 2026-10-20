<?php

declare(strict_types=1);

namespace App\Tests\Functional\Promotion\Action;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\Promotion;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Promotion\Action\GiftProductVariantPromotionActionCommand;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Repository\PromotionRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GiftProductVariantPromotionActionCommandTest extends WebTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testItCorrectlyAddsTheConfiguredVariantsToTheOrder(): Order
    {
        // The tokenValue 5811cf75-84a3-4673-9cc3-00755d1b72as is for an empty Order.
        self::createClient()->request('PUT', '/shop-api/default/fr_FR/carts/5811cf75-84a3-4673-9cc3-00755d1b72as/coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'coupon' => 'BFCOUETTE',
        ]);

        self::assertResponseIsSuccessful();

        /** @var OrderRepository $orderRepository */
        $orderRepository = self::$container->get(OrderRepository::class);

        $order = $orderRepository->findCartByTokenValue('5811cf75-84a3-4673-9cc3-00755d1b72as');

        self::assertInstanceOf(Order::class, $order);

        /** @var Channel $channel */
        $channel = $order->getChannel();

        $items = $order->getItems();
        self::assertCount(1, $items);

        /** @var OrderItem $addedItem */
        $addedItem = $items[0];
        self::assertSame('TED140190', $addedItem->getVariant()->getCode());
        self::assertEquals(6900, $addedItem->getVariant()->getChannelPricingForChannel($channel)->getPrice());
        self::assertCount(1, $addedItem->getAdjustments(AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT));
        self::assertEquals(-6900, $addedItem->getAdjustmentsTotal(AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT));
        self::assertEquals(0, $addedItem->getTotal());

        self::assertCount(1, $addedItem->getAdjustments('gifted_product_variant_BFCOUETE_PROMO'));
        self::assertEquals(0, $addedItem->getAdjustmentsTotal('gifted_product_variant_BFCOUETE_PROMO'));

        /** @var AdjustmentInterface $promoAdjustment */
        $promoAdjustment = $addedItem->getAdjustments('gifted_product_variant_BFCOUETE_PROMO')->first();
        self::assertTrue($promoAdjustment->isNeutral());

        return $order;
    }

    /**
     * @depends testItCorrectlyAddsTheConfiguredVariantsToTheOrder
     */
    public function testItCorrectlyRemovesTheVariantsAddedByThePromotion(Order $order): void
    {
        $configuration = [
            'variants' => [
                'TED140190',
            ],
        ];

        /** @var PromotionRepositoryInterface $promotionRepository */
        $promotionRepository = self::$container->get('sylius.repository.promotion');

        /** @var Promotion $promotion */
        $promotion = $promotionRepository->findOneBy(['code' => 'BFCOUETE_PROMO']);

        $command = self::$container->get(GiftProductVariantPromotionActionCommand::class);
        $command->revert($order, $configuration, $promotion);

        self::assertCount(0, $order->getItems());
    }
}
