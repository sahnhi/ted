<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Repository\PromotionCategoryRepository;
use App\Provider\CrossSelling\RelatedProductsProvider;
use App\View\AdjustmentView;
use App\View\CartSummaryView;
use App\View\RelatedProductView;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;

final class CartViewFactory implements CartViewFactoryInterface
{
    public function __construct(
        private readonly CartItemViewFactoryInterface $cartItemFactory,
        private readonly AddressViewFactoryInterface $addressViewFactory,
        private readonly TotalViewFactoryInterface $totalViewFactory,
        private readonly ShipmentViewFactoryInterface $shipmentViewFactory,
        private readonly PaymentViewFactoryInterface $paymentViewFactory,
        private readonly AdjustmentViewFactoryInterface $adjustmentViewFactory,
        private readonly CustomerViewFactoryInterface $customerViewFactory,
        private readonly RelatedProductsProvider $relatedProductsProvider,
        private readonly RelatedProductViewFactoryInterface $relatedProductViewFactory,
        private readonly PromotionCategoryRepository $promotionCategoryRepository,
    ) {
    }

    public function create(OrderInterface $cart, string $localeCode, bool $withVariants = false): CartSummaryView
    {
        /** @var Channel $channel */
        $channel = $cart->getChannel();

        $cartView = new CartSummaryView();
        $cartView->number = $cart->getNumber();
        $cartView->channel = $channel->getCode();
        $cartView->currency = $cart->getCurrencyCode();
        $cartView->locale = $localeCode;
        $cartView->checkoutState = $cart->getCheckoutState();
        $cartView->checkoutCompletedAt = $cart->getCheckoutCompletedAt();
        $cartView->tokenValue = $cart->getTokenValue();
        $cartView->totals = $this->totalViewFactory->create($cart);

        if (null !== $cart->getCustomer()) {
            $cartView->customer = $this->customerViewFactory->create($cart->getCustomer(), $cart->getTokenValue());
        }

        /** @var OrderItem $item */
        foreach ($cart->getItems() as $item) {
            $cartView->items[] = $this->cartItemFactory->create($item, $channel, $localeCode, $withVariants);
        }

        $cartView->relatedProducts = array_map(
            fn (Product $relatedProduct): RelatedProductView => $this->relatedProductViewFactory->create($relatedProduct, $channel, $localeCode),
            $this->relatedProductsProvider->provideForCart($cart)
        );

        foreach ($cart->getShipments() as $shipment) {
            $cartView->shipments[] = $this->shipmentViewFactory->create($shipment, $localeCode);
        }

        foreach ($cart->getPayments() as $payment) {
            $cartView->payments[] = $this->paymentViewFactory->create($payment, $localeCode);
        }

        /** @var AdjustmentView[] $cartDiscounts */
        $cartDiscounts = [];

        $promotionAdjustments = [
            ...$cart->getAdjustmentsRecursively(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT),
            ...$cart->getAdjustmentsRecursively(AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT),
            ...$cart->getAdjustmentsRecursively(AdjustmentInterface::ORDER_UNIT_PROMOTION_ADJUSTMENT),
        ];

        /** @var AdjustmentInterface $adjustment */
        foreach ($promotionAdjustments as $adjustment) {
            $originCode = $adjustment->getOriginCode();
            $additionalAmount = isset($cartDiscounts[$originCode]) ? $cartDiscounts[$originCode]->amount->current : 0;
            $cartDiscounts[$originCode] = $this->adjustmentViewFactory->create($adjustment, $additionalAmount, $cart->getCurrencyCode());
        }

        $cartView->cartDiscounts = $cartDiscounts;

        if (null !== $cart->getShippingAddress()) {
            $cartView->shippingAddress = $this->addressViewFactory->create($cart->getShippingAddress());
        }

        if (null !== $cart->getBillingAddress()) {
            $cartView->billingAddress = $this->addressViewFactory->create($cart->getBillingAddress());
        }

        $cartView->lingotMessage = $cart->getLingotPromotionMessage();

        $promotionCategories = $this->promotionCategoryRepository->findEnabledPromotionCategory();
        $data = [];
        foreach ($promotionCategories as $promotionCategory) {
            $cartView->gamification[$promotionCategory->getCode()] = [];
            $category = [];
            foreach ($promotionCategory->getPromotionCategoryPromotions() as $promotionCategoryPromotion) {
                $promotion = $promotionCategoryPromotion->getPromotion();
                $step = $rules = $actions = [];
                $step['code'] = $promotion->getCode();
                $step['apply'] = false;
                if (isset($cartView->cartDiscounts[$promotion->getCode()])) {
                    $step['apply'] = true;
                }
                $step['priority'] = $promotionCategoryPromotion->getPriority();
                foreach ($promotion->getRules() as $rule) {
                    $rules[$rule->getType()] = $rule->getConfiguration()['default'] ?? $rule->getConfiguration();
                }
                $step['rules'] = $rules;
                foreach ($promotion->getActions() as $action) {
                    $actions[$action->getType()] = $action->getConfiguration()['default'] ?? $action->getConfiguration();
                }
                $step['actions'] = $actions;
                $category[$promotionCategory->getCode()][] = $step;
            }
            $data[] = $category;
        }
        $cartView->gamification = $data;

        return $cartView;
    }
}
