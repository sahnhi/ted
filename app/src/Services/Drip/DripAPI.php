<?php

namespace App\Services\Drip;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Message\Drip;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Model\OrderInterface as OrderInterfaceAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @todo Change post array into objects then serialize it on post
 */
class DripAPI implements DripApiInterface
{
    public const TEDIBER_FRONT_URL = 'https://www.tediber.com/';
    public const TEDIBER_CART_URL = 'https://www.tediber.com/panier?token=';
    public const TEDIBER_RECAP_URL = 'https://www.tediber.com/recapitulatif?token=';

    public const BRAND = 'tediber';

    public const STATE_CREATED = 'created';
    public const STATE_PLACED = 'placed';
    public const STATE_PAID = 'paid';
    public const STATE_REFUNDED = 'refunded';
    public const STATE_FULFILLED = 'fulfilled';
    public const STATE_CANCELLED = 'canceled';
    public const STATE_UPDATED = 'updated';

    public function __construct(
        private readonly string $dripAccountId,
        private readonly MessageBusInterface $bus,
        private readonly ReportingInterface $reporting,
        private readonly HttpClientInterface $dripClient
    ) {
    }

    public function sendOrder(OrderInterface $order, array $customFields = []): void
    {
        if (is_null($order->getId())) {
            return;
        }

        $customFields = $this->updateCustomFields($order, $customFields);

        if ($order->getCustomer()) {
            if ('cart' === $order->getState()) {
                $activity = 'cart';
                $post = $this->constructCartPostBody([$order], $customFields);
            } else {
                $activity = 'order';
                $post = $this->constructOrderPostBody([$order], $customFields);
            }
            $apiUrl = '/v3/' . $this->dripAccountId . '/shopper_activity/' . $activity;

            $this->bus->dispatch(new Drip($apiUrl, $post));

            if ($order->getCustomer()->getEmail()) {
                $this->sendTag($order->getCustomer()->getEmail(), [$order->getChannel()->getName()]);
            }
        }
    }

    public function sendBatchOrders(array $orders): void
    {
        $apiUrl = '/v3/' . $this->dripAccountId . '/shopper_activity/order/batch';
        $post = $this->constructOrderPostBody($orders);

        $this->bus->dispatch(new Drip($apiUrl, $post));
    }

    public function sendCustomer(Customer $customer, array $tags = [], array $customFields = []): void
    {
        $apiUrl = '/v2/' . $this->dripAccountId . '/subscribers';
        $post = $this->constructCustomerPostBody([$customer], $tags, $customFields);

        $this->bus->dispatch(new Drip($apiUrl, $post));
    }

    public function sendBatchCustomers(array $customers): void
    {
        $apiUrl = '/v2/' . $this->dripAccountId . '/subscribers/batches';
        $post = $this->constructCustomerPostBody($customers);

        $this->bus->dispatch(new Drip($apiUrl, $post));
    }

    public function sendTag(string $email, array $tags): void
    {
        $apiUrl = '/v2/' . $this->dripAccountId . '/tags';
        $post = $this->constructTagsPostBody($email, $tags);

        $this->bus->dispatch(new Drip($apiUrl, $post));
    }

    public function sendEvent(array $events): void
    {
        $apiUrl = '/v2/' . $this->dripAccountId . '/events';
        $post = $this->constructEventsPostBody($events);

        $this->bus->dispatch(new Drip($apiUrl, $post));
    }

    public function sendBatchProductReviews(array $productReviews): string
    {
        $apiUrl = '/v2/' . $this->dripAccountId . '/events/batches';
        $post = $this->constructProductReviewPostBody($productReviews);

        return $this->request($apiUrl, $post);
    }

    public function sendBatchNPS(array $orders): string
    {
        $apiUrl = '/v2/' . $this->dripAccountId . '/events/batches';
        $post = $this->constructNPSPostBody($orders);

        return $this->request($apiUrl, $post);
    }

    public function sendBatchProducts(array $variants, $channel): string
    {
        $apiUrl = '/v3/' . $this->dripAccountId . '/shopper_activity/product/batch';
        $post = $this->constructProductsCreateBody($variants, $channel);

        return $this->request($apiUrl, $post);
    }

    private function updateCustomFields(OrderInterface $order, array $customFields = []): array
    {
        if ($shipment = $order->getShipments()->last()) {
            /** @var Shipment $shipment */
            $customFields = array_merge($customFields, [
                'shippingDate' => ($shipment->getEndDeliverySlot() ? $shipment->getEndDeliverySlot()->format('Y-m-d') : ''),
                'startSlotHour' => ($shipment->getStartDeliverySlot() ? $shipment->getStartDeliverySlot()->format('H:i:s') : ''),
                'endSlotHour' => ($shipment->getEndDeliverySlot() ? $shipment->getEndDeliverySlot()->format('H:i:s') : ''),
            ]);
        }

        if ($order->getOrderStore()) {
            $customFields = array_merge($customFields, [
                'store' => ($order->getOrderStore()->getStore() ? $order->getOrderStore()->getStore()->getName() : ''),
                'seller' => ($order->getOrderStore()->getSeller() ? $order->getOrderStore()->getSeller()->getName() : ''),
            ]);
        }

        return $customFields;
    }

    private function constructTagsPostBody(string $email, array $tags): array
    {
        $post = [];
        foreach ($tags as $tag) {
            $post['tags'][] = ['email' => $email, 'tag' => $tag];
        }

        return $post;
    }

    private function constructCustomerPostBody(array $customers, array $tags = [], array $customFields = []): array
    {
        $batchPost['batches'] = [];
        $post['subscribers'] = [];

        foreach ($customers as $customer) {
            if (null !== $customer->getNbFilleul() && null !== $customer->getParrainOrderAmountTotal()) {
                $customFields['nb_filleul'] = $customer->getNbFilleul();
                $customFields['total'] = $customer->getParrainOrderAmountTotal();
            }

            if (null !== $customer->getSponsorCoupon()) {
                $customFields['sponsor_coupon'] = $customer->getSponsorCoupon();
            }
            $allTags = array_merge(['sylius'], $tags);
            $customFields['newsletter'] = $customer->isSubscribedToNewsletter();
            $customFields['optinPhone'] = $customer->isOptinPhone();
            $post['subscribers'][] = [
                'email' => $customer->getEmail(),
                'user_id' => (string) $customer->getId(),
                'tags' => $allTags,
                'first_name' => $customer->getFirstName() ?? '',
                'last_name' => $customer->getLastName() ?? '',
                'phone' => $customer->getPhoneNumber() ?? '',
                'custom_fields' => $customFields,
            ];

            if ($customer->getDefaultAddress()) {
                $post['address1'] = $customer->getDefaultAddress()->getStreet() ?? '';
                $post['zip'] = $customer->getDefaultAddress()->getPostcode() ?? '';
                $post['city'] = $customer->getDefaultAddress()->getCity() ?? '';
                $post['country'] = $customer->getDefaultAddress()->getCountryCode() ?? '';
            }
        }

        if (count($customers) > 1) {
            $batchPost['batches'][] = $post;
        }

        return count($customers) > 1 ? $batchPost : $post;
    }

    private function constructBasePostBody(OrderInterface $order, array $customFields = []): array
    {
        $items = [];
        foreach ($order->getItems() as $orderItem) {
            /** @var ProductVariantInterface $variant */
            $variant = $orderItem->getVariant();

            $items[] = [
                'product_id' => (string) $orderItem->getProduct()->getId(),
                'product_variant_id' => (string) $variant->getId(),
                'sku' => $variant->getCode(),
                'name' => $variant->getName(),
                'brand' => self::BRAND,
                'price' => abs($orderItem->getUnitPrice() / 100),
                'quantity' => $orderItem->getQuantity(),
                'discount' => abs($orderItem->getDiscountAmount() / 100),
                'total' => abs($orderItem->getTotal() / 100),
                'image_url' => 'https://res.cloudinary.com/dgodjz1pn/image/upload/c_scale,dpr_auto,f_auto,q_auto,w_auto:120:400/v1/tediber/products/' . $orderItem->getProduct()->getCode() . '/main',
                'product_url' => $this->getUrlForVariant($variant),
            ];
        }

        if ($order->getCustomer() && !str_contains((string) $order->getCustomer()->getEmail(), 'bug.fr')) {
            $email = $order->getCustomer()->getEmail();
        } else {
            $email = 'bug@bug.fr';
        }

        $post = [
            'provider' => 'sylius',
            'action' => $this->getOrderStatus($order),
            'email' => $email,
            'grand_total' => abs($order->getTotal() / 100),
            'total_produit' => abs($order->getItemsTotal() / 100),
            'total_discounts' => abs($order->getOrderPromotionTotal() / 100),
            'currency' => $order->getCurrencyCode(),
            'items' => $items,
            'channel' => $order->getChannel()->getName(),
            'checkout_state' => $order->getCheckoutState(),
            'payment_state' => $order->getPaymentState(),
            'shipping_state' => $order->getShippingState(),
            'cart_token' => $order->getTokenValue() ?? '',
            'custom_fields' => [$customFields],
        ];

        if ($order->getPromotionCoupon()) {
            $post['promotion'] = $order->getPromotionCoupon()->getPromotion()->getName();
        }

        return $post;
    }

    private function constructCartPostBody(array $orders, array $customFields = []): array
    {
        $batchPost['orders'] = [];
        $post = [];
        foreach ($orders as $order) {
            $post = $this->constructBasePostBody($order, $customFields);
            $post['cart_id'] = (string) $order->getId();

            if ($order->getTokenValue()) {
                $post['cart_public_id'] = $order->getTokenValue();
                $post['cart_url'] = self::TEDIBER_CART_URL . $order->getTokenValue();
            }

            if (count($orders) > 1) {
                $batchPost['orders'][] = $post;
            }
        }

        return count($orders) > 1 ? $batchPost : $post;
    }

    private function constructOrderPostBody(array $orders, array $customFields = []): array
    {
        $batchPost['orders'] = [];
        $post = [];

        foreach ($orders as $order) {
            $post = $this->constructBasePostBody($order, $customFields);

            // Drip bug when cart_id and order_id are the same
            // Hence the order number instead
            $post['order_id'] = (string) $order->getNumber();
            $post['order_public_id'] = (string) $order->getNumber();

            if ($order->getTokenValue()) {
                $post['order_url'] = self::TEDIBER_RECAP_URL . $order->getTokenValue();
            }

            if ($order->getShippingAddress()) {
                $shippingAddress = ['first_name' => $order->getShippingAddress()->getFirstName() ?? '', 'last_name' => $order->getShippingAddress()->getLastName() ?? '', 'address_1' => $order->getShippingAddress()->getStreet() ?? '', 'city' => $order->getShippingAddress()->getCity() ?? '', 'postal_code' => $order->getShippingAddress()->getPostcode() ?? '', 'country' => $order->getShippingAddress()->getCountryCode() ?? '', 'company' => $order->getShippingAddress()->getCompany() ?? '', 'phone' => $order->getShippingAddress()->getPhoneNumber() ?? ''];
                $post['shipping_address'] = $shippingAddress;
            }

            if ($order->getBillingAddress()) {
                $billingAddress = ['first_name' => $order->getBillingAddress()->getFirstName() ?? '', 'last_name' => $order->getBillingAddress()->getLastName() ?? '', 'address_1' => $order->getBillingAddress()->getStreet() ?? '', 'city' => $order->getBillingAddress()->getCity() ?? '', 'postal_code' => $order->getBillingAddress()->getPostcode() ?? '', 'country' => $order->getBillingAddress()->getCountryCode() ?? '', 'company' => $order->getBillingAddress()->getCompany() ?? '', 'phone' => $order->getBillingAddress()->getPhoneNumber() ?? ''];
                $post['billing_address'] = $billingAddress;
            }

            /** @var PaymentInterface $lastPayment */
            $lastPayment = $order->getLastPaymentByStateOrReturnLast(PaymentInterface::STATE_COMPLETED);
            if (null !== $lastPayment && $lastPayment->getMethod()) {
                $post['payment'] = $lastPayment->getMethod()->getName();
            }

            if (!$order->getShipments()->isEmpty() && $order->getShipments()->last()->getStartDeliverySlot()) {
                $post['shipment_name'] = $order->getShipments()->last()->getMethod()->getName();
                $post['shipment_start_slot'] = $order->getShipments()->last()->getStartDeliverySlot();
                $post['shipment_end_slot'] = $order->getShipments()->last()->getEndDeliverySlot();
            }

            if (count($orders) > 1) {
                $batchPost['orders'][] = $post;
            }
        }

        return count($orders) > 1 ? $batchPost : $post;
    }

    private function constructEventsPostBody(array $events): array
    {
        $post = [];
        foreach ($events as $event) {
            $post['events'][] = $event;
        }

        return $post;
    }

    private function constructProductReviewPostBody(array $productReviews): array
    {
        $batchPost['batches'] = [];
        $post['events'] = [];
        foreach ($productReviews as $productReview) {
            $post['events'][] = [
                'email' => $productReview->getAuthor()->getEmail(),
                'action' => sprintf('Left a %s star review on %s', $productReview->getRating(), $productReview->getReviewSubject()->getCode()),
            ];
        }

        $batchPost['batches'][] = $post;

        return $batchPost;
    }

    private function constructNPSPostBody(array $orders): array
    {
        $batchPost['batches'] = [];
        $post['events'] = [];
        /** @var OrderInterface $order */
        foreach ($orders as $order) {
            $status = 'detractor';

            if (null === $order || empty($order->getNPS())) {
                continue;
            }

            $nps = $order->getNPS();
            if ($nps > 6 && $nps < 9) {
                $status = 'neutral';
            } elseif ($nps >= 9) {
                $status = 'promoter';
            }

            $post['events'][] = [
                'email' => $order->getCustomer()->getEmail(),
                'action' => sprintf('Became a %s', $status),
            ];
        }

        $batchPost['batches'][] = $post;

        return $batchPost;
    }

    private function getOrderStatus(OrderInterface $order): string
    {
        $status = self::STATE_UPDATED;
        if (OrderInterfaceAlias::STATE_CART === $order->getState()) {
            if (OrderCheckoutStates::STATE_CART === $order->getCheckoutState()
                || OrderCheckoutStates::STATE_ADDRESSED === $order->getCheckoutState()) {
                $status = self::STATE_CREATED;
            }
        } elseif (OrderInterfaceAlias::STATE_NEW === $order->getState()) {
            if (OrderPaymentStates::STATE_AWAITING_PAYMENT === $order->getPaymentState()) {
                $status = self::STATE_PLACED;
            } elseif (OrderPaymentStates::STATE_PAID === $order->getPaymentState()) {
                $status = self::STATE_PAID;
            } elseif (OrderPaymentStates::STATE_REFUNDED === $order->getPaymentState()) {
                $status = self::STATE_REFUNDED;
            }
        } elseif (OrderInterfaceAlias::STATE_FULFILLED === $order->getState()) {
            $status = self::STATE_FULFILLED;
        } elseif (OrderInterfaceAlias::STATE_CANCELLED === $order->getState()) {
            $status = self::STATE_CANCELLED;
        }

        return $status;
    }

    public function request(string $apiUrl, array $post): string
    {
        $message = '<info>All good!</info>';
        try {
            $this->dripClient->request(Request::METHOD_POST, $apiUrl, ['json' => $post]);
        } catch (\Throwable $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['drip_url' => $apiUrl],
                ['payload' => $post]
            );
            $message = '<error>' . $e->getMessage() . '<error>';
        }

        return $message;
    }

    public function constructProductsCreateBody(array $variants, ChannelInterface $channel): array
    {
        $batchPost['products'] = [];

        /** @var ProductVariantInterface $variant */
        foreach ($variants as $variant) {
            $pricing = $variant->getChannelPricingForChannel($channel);
            $price = null === $pricing ? 0 : $pricing->getPrice() / 100;

            $post = [
                'provider' => 'sylius',
                'action' => 'created',
                'product_id' => (string) $variant->getProduct()->getId(),
                'product_variant_id' => (string) $variant->getId(),
                'sku' => $variant->getCode(),
                'name' => $variant->getName(),
                'brand' => self::BRAND,
                'price' => $price,
                'image_url' => 'https://res.cloudinary.com/dgodjz1pn/image/upload/c_scale,dpr_auto,f_auto,q_auto,w_auto:120:400/v1/products/' . $variant->getProduct()->getCode() . '/cart/' . $variant->getProduct()->getCode(),
                'product_url' => $this->getUrlForVariant($variant),
                'inventory' => 100000,
            ];

            $batchPost['products'][] = $post;
        }

        return $batchPost;
    }

    private function getUrlForVariant(ProductVariantInterface $variant): string
    {
        $urls = [
            'slim-mattress' => 'matelas',
            'bed-base' => 'sommier',
            'sofa-structure' => 'canape-convertible',
            'storage-bed-frame-structure' => 'sommier-coffre',
            'bed-linen-pillow-case-gauze' => 'taie-oreiller',
        ];

        $url = array_key_exists($variant->getProduct()->getCode(), $urls) ? $urls[$variant->getProduct()->getCode()] : $variant->getProduct()->getSlug();

        return self::TEDIBER_FRONT_URL . $url;
    }
}
