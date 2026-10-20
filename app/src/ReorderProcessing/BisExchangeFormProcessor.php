<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class BisExchangeFormProcessor implements ReorderProcessor
{
    private readonly ?Request $request;

    public function __construct(
        RequestStack $requestStack,
        private readonly RepositoryInterface $reasonRepository,
        private readonly RepositoryInterface $productVariantRepository,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly RepositoryInterface $adminUserRepository,
        private readonly FactoryInterface $orderItemFactory,
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        $formData = $this->request->get('sylius_admin_order_creation_new_order');
        Assert::isArray($formData, 'New order form not submited correctly');

        $this->handleReasonFormData($formData, $reorder);
        $this->handleProductVariantFormData($formData, $reorder);
    }

    /**
     * @param Order $order
     */
    private function handleReasonFormData(array $formData, OrderInterface $order): OrderInterface
    {
        if (!empty($formData['bisExchange'])) {
            foreach ($formData['bisExchange'] as $reasonData) {
                $reason = isset($reasonData['reason']) ? $this->reasonRepository->findOneBy(['id' => $reasonData['reason']]) : null;
                $productVariant = isset($reasonData['variant']) ? $this->productVariantRepository->findOneBy(['code' => $reasonData['variant']]) : null;
                $offer = isset($reasonData['commercialOffer']) ? $this->reasonRepository->findOneBy(['id' => $reasonData['commercialOffer']]) : null;

                $orderReason = new OrderReason();
                $orderReason->setReason($reason);
                $orderReason->setOrder($order);
                $orderReason->setIsProblem(false);

                if (isset($reasonData['createdById'])) {
                    $user ??= $this->adminUserRepository->findOneBy(['id' => $reasonData['createdById']]);

                    if (null !== $user) {
                        $orderReason->setCreatedBy($user);
                    }
                }

                if (null !== $productVariant) {
                    $orderReason->setVariant($productVariant);
                }

                Assert::keyExists($reasonData, 'quantity', 'Quantity cannot be null');
                $orderReason->setQuantity((int) $reasonData['quantity']);

                if (null !== $offer) {
                    $orderReason->setCommercialOffer($offer);
                }

                $orderReason->setComment($reasonData['comment'] ?? null);
                $order->addOrderReason($orderReason);
            }
        }

        return $order;
    }

    private function handleProductVariantFormData(array $formData, OrderInterface $order): void
    {
        if (!empty($formData['bisItems'])) {
            foreach ($order->getItems() as $item) {
                $this->orderItemQuantityModifier->modify($item, 0);
            }

            $order->getItems()->clear();
            $order->getItemUnits()->clear();

            foreach ($formData['bisItems'] as $bisItem) {
                Assert::keyExists($bisItem, 'variant', 'Variant cannot be empty');
                $productVariant = $this->productVariantRepository->findOneBy(['code' => $bisItem['variant']]);

                Assert::notNull($productVariant, 'ProductVariant Not Found');
                Assert::notNull($productVariant->getProduct(), 'ProductVariant has no Product');
                Assert::keyExists($bisItem, 'quantity', 'Quantity cannot be empty');

                /** @var OrderItem $newItem */
                $newItem = $this->orderItemFactory->createNew();
                $newItem->setVariant($productVariant);
                $newItem->setUnitPrice(0);
                $newItem->setItemTotal(0);
                $newItem->setProductName($productVariant->getProduct()->getName());
                $newItem->setVariantName($productVariant->getName());

                $this->orderItemQuantityModifier->modify($newItem, (int) $bisItem['quantity']);

                $order->addItem($newItem);
            }
        }
    }
}
