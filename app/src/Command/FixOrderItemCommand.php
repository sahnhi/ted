<?php

namespace App\Command;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\ProductComboRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Services\Sentry\Reporting;
use Doctrine\ORM\EntityManagerInterface;
use Sentry\Severity;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FixOrderItemCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ProductComboRepository $productComboRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly Reporting $reporting
    ) {
        parent::__construct('app:fix-order-item');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array $orders */
        $orders = $this->orderRepository->getOrdersOrderItems();
        $progressBar = new ProgressBar($output, count($orders));
        $progressBar->setFormat('debug');
        $progressBar->start();

        /** @var Order $order */
        foreach ($progressBar->iterate($orders) as $order) {
            $orderItems = $order->getItems();
            foreach ($orderItems as $item) {
                if (0 === count($item->getOrderItemLogistics())) {
                    $order->removeItem($item);
                } elseif ($item->getQuantity() !== count($item->getOrderItemLogistics())
                    && $item->getVariant()->getCode() === $item->getOrderItemLogistics()->first()->getProductVariantLogistic()->getCode()) {
                    $this->orderItemQuantityModifier->modify($item, count($item->getOrderItemLogistics()));
                } elseif (1 < count($item->getOrderItemLogistics())) {
                    $variant = $item->getVariant();
                    if (!$variant instanceof ProductVariant) {
                        continue;
                    }
                    $variantCombo = $this->productComboRepository->findBy(['productVariant' => $variant->getId()]);
                    $arrayVariantLogistics = [];
                    foreach ($item->getOrderItemLogistics() as $logistic) {
                        if (!isset($arrayVariantLogistics[$logistic->getProductVariantLogistic()->getCode()])) {
                            $arrayVariantLogistics[$logistic->getProductVariantLogistic()->getCode()] = 1;
                        } else {
                            ++$arrayVariantLogistics[$logistic->getProductVariantLogistic()->getCode()];
                        }
                    }
                    $quantityTotal = 0;
                    foreach ($arrayVariantLogistics as $code => $quantity) {
                        $currentQuantity = $quantity;
                        while (0 < $currentQuantity) {
                            if (isset($arrayVariantLogistics[$item->getVariant()->getCode()]) && $arrayVariantLogistics[$item->getVariant()->getCode()] > 0) {
                                --$arrayVariantLogistics[$item->getVariant()->getCode()];
                                --$currentQuantity;
                                ++$quantityTotal;
                            } else {
                                if ($variant->isBundle()) {
                                    foreach ($variant->getBundleAssociations() as $key => $association) {
                                        if (isset($arrayVariantLogistics[$association->getVariant()->getCode()])
                                            && $code === $association->getVariant()->getCode()) {
                                            $arrayVariantLogistics[$association->getVariant()->getCode()] -= $association->getQuantity();
                                            $currentQuantity -= $association->getQuantity();
                                            if ($key === count($variant->getBundleAssociations()) - 1) {
                                                ++$quantityTotal;
                                            }
                                        }
                                    }
                                } else {
                                    if (!empty($variantCombo)) {
                                        foreach ($variantCombo as $key => $combo) {
                                            if (isset($arrayVariantLogistics[$combo->getProductVariantLogistic()->getCode()])
                                                && $code === $combo->getProductVariantLogistic()->getCode()) {
                                                $arrayVariantLogistics[$combo->getProductVariantLogistic()->getCode()] -= $combo->getQuantity();
                                                $currentQuantity -= $combo->getQuantity();
                                                if ($key === array_key_last($variantCombo)) {
                                                    ++$quantityTotal;
                                                }
                                            } else {
                                                $productComboChild = $this->productComboRepository->findOneBy(['productVariant' => $combo->getProductVariantLogistic()->getId()]);
                                                if (null === $productComboChild && array_key_exists('TED2O6060', $arrayVariantLogistics)) {
                                                    $productComboChild = $this->productComboRepository->findOneBy(['productVariantLogistic' => $combo->getProductVariantLogistic()->getId()]);
                                                    --$arrayVariantLogistics[$productComboChild->getProductVariantLogistic()->getCode()];
                                                    --$currentQuantity;
                                                }

                                                if (!empty($productComboChild)
                                                    && !empty($productComboChild->getProductVariantLogistic())
                                                    && isset($arrayVariantLogistics[$productComboChild->getProductVariantLogistic()->getCode()])
                                                    && $code === $productComboChild->getProductVariantLogistic()->getCode()
                                                ) {
                                                    $arrayVariantLogistics[$productComboChild->getProductVariantLogistic()->getCode()] -= $productComboChild->getQuantity();
                                                    $currentQuantity -= $productComboChild->getQuantity();
                                                    if ($key === array_key_last($variantCombo)) {
                                                        ++$quantityTotal;
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $this->reporting->reportIssue(
                                            'fix order command item, empty combo',
                                            Severity::info(),
                                            [
                                                'order_number' => $order->getId(),
                                                'variant_code' => $variant->getCode(),
                                            ]
                                        );
                                        continue 3;
                                    }
                                }
                            }
                        }
                    }
                    $this->orderItemQuantityModifier->modify($item, $quantityTotal);
                }
            }
            if (OrderCheckoutStates::STATE_PAYMENT_SELECTED === $order->getCheckoutState()
            && OrderPaymentStates::STATE_PAID === $order->getPaymentState()
            ) {
                $order->setCheckoutState(OrderCheckoutStates::STATE_COMPLETED);
            }
            $this->entityManager->persist($order);
        }
        $this->entityManager->flush();
        $progressBar->finish();

        $output->writeln('Order Items fixed successfully');

        return 0;
    }

    protected function configure(): void
    {
        $this->setDescription('Fix Order items for order with excess orderItems');
    }
}
