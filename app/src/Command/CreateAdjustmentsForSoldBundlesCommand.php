<?php

declare(strict_types=1);

namespace App\Command;

use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\PromotionInterface;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use App\Factory\PromotionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Repository\PromotionRepositoryInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAdjustmentsForSoldBundlesCommand extends Command
{
    protected static $defaultName = 'app:create-adjustments-for-sold-bundles';

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly PromotionRepositoryInterface $promotionRepository,
        private readonly PromotionFactory $promotionFactory
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->orderRepository->createQueryBuilder('o')
                                        ->andWhere('o.customer is not null')
                                        ->andWhere('o.createdAt >= :dateSince')
                                        ->setParameter('dateSince', new \DateTime('2024-03-27'))
                                        ->getQuery()
                                        ->toIterable()
        ;

        $count = 0;
        $batchSize = 20;
        $bundleProductCodes = [
            'bundle-lit-adult',
            'bundle-literie-adult',
            'bundle-lit-kid',
            'bundle-literie-kid',
        ];

        $promotions = [
            'bundle-literie-kid' => 'pack_promo_packcouetteoreiller_enfant',
            'bundle-literie-adult' => 'pack_promo_packcouetteoreiller_adulte',
            'bundle-lit-kid' => 'pack_promo_packlitenfant',
            'bundle-lit-adult' => 'pack_promo_packlitadulte',
        ];

        foreach ($promotions as $productCode => $promotionCode) {
            $promotions[$productCode] = $this->promotionRepository->findOneBy(['code' => $promotionCode]);
            if (null === $promotions[$productCode]) {
                $bundleLitPromotion = $this->promotionFactory->createNew();
                $bundleLitPromotion->setCode($promotionCode);
                $bundleLitPromotion->setName($promotionCode);
                $bundleLitPromotion->setEndsAt(new \DateTime('yesterday'));
                $promotions[$productCode] = $bundleLitPromotion;
                $this->entityManager->persist($bundleLitPromotion);
            }
        }

        /** @var Order $order */
        foreach ($orders as $order) {
            /** @var OrderItem $orderItem */
            foreach ($order->getItems() as $orderItem) {
                if (in_array($orderItem->getVariant()->getProduct()->getCode(), $bundleProductCodes, true)) {
                    /** @var OrderItemUnit $unit */
                    foreach ($orderItem->getUnits() as $unit) {
                        $variantPricing = $orderItem->getVariant()?->getChannelPricingForChannel(
                            $orderItem->getOrder()->getchannel()
                        );
                        if ($variantPricing && $variantPricing->getPrice() && $variantPricing->getOriginalPrice()) {
                            /** @var PromotionInterface $promotion */
                            $promotion = $promotions[$orderItem->getVariant()->getProduct()->getCode()];
                            $percentage = round($variantPricing->getPrice() / $variantPricing->getOriginalPrice(), 2);
                            $stringPercent = (1 - $percentage) * 100;
                            $adjValue = $variantPricing->getPrice() - $variantPricing->getOriginalPrice();
                            if (0 === $adjValue) {
                                continue;
                            }

                            $adjustment = $this->adjustmentFactory->createWithData(
                                AdjustmentInterface::ORDER_UNIT_PROMOTION_ADJUSTMENT,
                                sprintf('Promotion %s %%', $stringPercent),
                                $adjValue,
                            );
                            $adjustment->setOriginCode($promotion->getCode());
                            $adjustment->setCreatedAt($order->getCheckoutCompletedAt());

                            $order->addPromotion($promotion);
                            $unit->addAdjustment($adjustment);
                            $orderItem->setUnitPrice($variantPricing->getOriginalPrice());

                            $this->entityManager->persist($adjustment);
                            ++$count;

                            if (0 === ($count % $batchSize)) {
                                $this->entityManager->flush();
                            }
                        }
                    }
                }
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Fix bundle association prices
        $bundleProducts = $this->productRepository->findBy(['code' => $bundleProductCodes]);
        /** @var Product $product */
        foreach ($bundleProducts as $product) {
            if (in_array($product->getCode(), ['bundle-literie-adult', 'bundle-literie-kid'], true)) {
                /** @var ProductVariant $productVariant */
                foreach ($product->getVariants() as $productVariant) {
                    $productVariant->getBundleAssociations()->map(
                        function (BundleAssociation $bundleAssociation): void {
                            $goodPrice = $bundleAssociation->getCurrentPriceObject();
                            $priceValue = $goodPrice->getPrice();
                            $previousPrice = $bundleAssociation->getPriceObjectAtDate(
                                \DateTime::createFromInterface($goodPrice->getCreatedAt())->modify('-1 day')
                            );
                            $bundleAssociation->removePrice($goodPrice);
                            $previousPrice->setPrice($priceValue);
                        }
                    );
                }
            } else {
                /** @var ProductVariant $productVariant */
                foreach ($product->getVariants() as $productVariant) {
                    $productVariant->getChannelPricings()->map(function (ChannelPricing $channelPricing): void {
                        if ($channelPricing->getPrice() && $channelPricing->getOriginalPrice()) {
                            $channelPricing->setPrice($channelPricing->getOriginalPrice());
                        }
                    });

                    $productVariant->getBundleAssociations()->map(
                        function (BundleAssociation $bundleAssociation): void {
                            $bundleAssociation->getCurrentPriceObject()->setPrice(
                                $bundleAssociation->getVariant()->getChannelPricings()->first()->getPrice()
                            );
                        }
                    );
                }
            }
        }

        $this->entityManager->flush();

        return 0;
    }
}
