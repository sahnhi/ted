<?php

namespace App\Services\CaptainMetrics;

use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Message\CaptainMetrics;
use App\Services\Sentry\ReportingInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Sentry;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Model\OrderInterface as OrderInterfaceAlias;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @todo: switch all array of POST values to DTO objects
 *
 * @todo: merge the same methods with DripAPI
 */
class CaptainMetricsApi implements CaptainMetricsApiInterface
{
    public const BASE_TVA = 1.2;

    public const STATUS_PARTIAL = 'partial';
    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUS_PAYMENT_PENDING = 'pending';
    public const STATUS_PAYMENT_REFUNDED = 'refunded';
    public const STATUS_PAYMENT_PAID = 'paid';
    public const STATUS_PAYMENT_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_PAYMENT_AUTHORIZED = 'authorized';
    private ClientInterface $client;

    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    public function __construct(
        private readonly string $projectId,
        private readonly string $token,
        private readonly MessageBusInterface $bus,
        private readonly RepositoryInterface $orderRepository,
        private readonly ReportingInterface $reporting
    ) {
        $this->client = new Client(['base_uri' => self::CAPTAIN_METRICS_URL]);
    }

    public function request(array $payload, string $tag): string
    {
        $uid = ['payload' => $payload, 'timestamp' => new \DateTime()];
        $request = [
            'id' => sha1(serialize($uid)),
            'projectId' => $this->projectId,
            'items' => $payload,
            'sync' => false,
        ];

        $message = '<info>CaptainMetrics call : done !</info>';
        try {
            $this->client->post('', ['json' => $request, 'headers' => ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']]);
        } catch (\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['captainmetrics_call' => $tag],
                ['payload' => $request]
            );
            $message = '<error>' . $e->getMessage() . '<error>';
        }

        return $message;
    }

    public function sendUser(Order $order): void
    {
        $user = $order->getCustomer();
        $channel = $order->getChannel();

        if (null === $channel || null === $user || null === $user->getId()) {
            return;
        }

        $item = $this->constructUserBody($user, $channel->getCode(), $order->getBillingAddress());

        $this->bus->dispatch(new CaptainMetrics([$item], 'sendUser'));
    }

    public function sendOrderStore(OrderStore $orderStore): void
    {
        $customer = $orderStore->getOrder()->getCustomer();

        $user = $this->constructUserBody($customer, $orderStore->getOrder()->getChannel()->getCode(), $orderStore->getOrder()->getBillingAddress());
        $session = $this->constructSessionBody($orderStore);
        $event = $this->constructEventBody($orderStore);

        $this->bus->dispatch(new CaptainMetrics([$user, $session, $event], 'sendOrderStore'));
    }

    public function sendOrder(Order $order): void
    {
        if (null === $order->getNumber() || null === $order->getId()) {
            return;
        }

        $item = $this->constructOrderBody($order);

        $this->bus->dispatch(new CaptainMetrics([$item], 'sendOrder'));
    }

    public function sendBatchUsers(array $users, string $channel = 'default'): string
    {
        $items = [];

        /** @var CustomerInterface $customer */
        foreach ($users as $customer) {
            $lastOrder = $customer->getOrders()->last();

            if ($lastOrder instanceof OrderInterface) {
                $items[] = $this->constructUserBody($customer, $channel, $lastOrder->getBillingAddress());
            }
        }

        return $this->request($items, 'batchUsers');
    }

    public function sendBatchOrderStores(array $orderStores): string
    {
        $items = [];
        /** @var OrderStore $orderStore */
        foreach ($orderStores as $orderStore) {
            $items[] = $this->constructUserBody($orderStore->getOrder()->getCustomer(), $orderStore->getOrder()->getChannel()->getCode(), $orderStore->getOrder()->getBillingAddress());
            $items[] = $this->constructSessionBody($orderStore);
            $items[] = $this->constructEventBody($orderStore);
        }

        return $this->request($items, 'batchUsers');
    }

    public function sendBatchOrders(array $orders): string
    {
        $items = [];
        foreach ($orders as $order) {
            $items[] = $this->constructOrderBody($order);
        }

        return $this->request($items, 'batchUsers');
    }

    private function constructUserBody(Customer $customer, string $channel, ?AddressInterface $address): array
    {
        $itemArray = [
            'id' => (string) $customer->getId(),
            'kind' => 'user',
            'user' => [
                'externalId' => (string) $customer->getId(),
                'isAuthenticated' => true,
                'createdAt' => $customer->getCreatedAt()->format('c'),
                'profile' => [
                    'set' => [
                        'signupAt' => $customer->getCreatedAt()->format('c'),
                        'firstname' => $customer->getFirstName(),
                        'lastname' => $customer->getLastName(),
                        'language' => $customer->getLocale(),
                        'email' => $customer->getEmail(),
                        'emailMd5' => md5((string) $customer->getEmail()),
                        'emailSha1' => sha1((string) $customer->getEmail()),
                        'emailSha256' => hash('sha256', (string) $customer->getEmail()),
                        'telephone' => $customer->getPhoneNumber(),
                        'timezone' => 'Europe/Paris',
                    ],
                    'addTags' => ['Sylius', $channel],
                ],
            ],
        ];

        if ($address instanceof AddressInterface) {
            $itemArray['user']['profile']['set']['addressLine1'] = $address->getStreet();
            $itemArray['user']['profile']['set']['city'] = $address->getCity();
            $itemArray['user']['profile']['set']['country'] = $address->getCountryCode();
            $itemArray['user']['profile']['set']['postalCode'] = $address->getPostcode();
        }

        if (null !== $customer->getBirthday()) {
            $itemArray['user']['profile']['set']['birthday'] = $customer->getBirthday()->format('Y-m-d');
        }

        return $itemArray;
    }

    /**
     * @param Order|null $order
     */
    private function constructOrderBody(Order $order): array
    {
        $original = $this->orderRepository->findInitialOrder($order->getNumber());

        // Get all orders linked to the original one
        $linkedOrders = $this->orderRepository->findAssociatedOrders($original->getNumber());

        $domain = $this->getDomain($original);

        $itemArray = [
            'id' => (string) $original->getId(),
            'kind' => 'order',
            'order' => [
                'externalId' => $original->getNumber(),
                'externalUserId' => (string) $original->getCustomer()->getId(),
                'userIsAuthenticated' => true,
                'createdAt' => $original->getCreatedAt()->format('c'),
                'updatedAt' => $this->orderRepository->getLastUpdatedDate($original->getNumber())->format('c'),
                'domainId' => $domain,
                'conversionRuleId' => 'ventes',
                'financialStatus' => $this->getPaymentStatus($linkedOrders),
                'currency' => 'EUR',
                'revenue' => $this->getRevenue($linkedOrders),
                'totalTax' => $original->getTaxTotal(),
                'totalDiscounts' => $original->getOrderPromotionTotal(),
                'fulfillmentStatus' => $this->getOrderStatus($linkedOrders),
                'publicURL' => 'https://www.tediber.com/panier?token=' . $original->getTokenValue(),
                'tags' => [$original->getChannel()->getCode(), 'Sylius'],
                'items' => [],
                'refunds' => $this->getOrderRefunds($linkedOrders),
            ],
        ];

        if (null !== $original->getCheckoutCompletedAt()) {
            $itemArray['order']['closedAt'] = $original->getCheckoutCompletedAt()->format('c');
        }

        if ($original->getChannel() !== $order->getChannel()) {
            $itemArray['order']['tags'][] = $order->getChannel()->getCode();
        }

        if (OrderInterfaceAlias::STATE_CANCELLED === $original->getState() && !$original->getOrderReason()->isEmpty() && null !== $original->getOrderReason()->first()->getReason()) {
            $itemArray['order']['cancelReason'] = $original->getOrderReason()->first()->getReason()->getName();
        }

        /* @var OrderItem $item */
        foreach ($linkedOrders as $orderArray) {
            foreach ($orderArray as $orderRow) {
                foreach ($orderRow->getItems() as $item) {
                    $taxes = 0;
                    foreach ($item->getUnits() as $unit) {
                        foreach ($unit->getAdjustments('tax') as $adjustment) {
                            $taxes += $adjustment->getAmount();
                        }
                    }

                    $priceHT = 0 === $taxes ? ceil($item->getTotal() / self::BASE_TVA) : $item->getTotal() - $taxes;

                    $id = $item->getVariant()->getCode();
                    $id = null === $orderRow->getParentNumber() ? $id : $orderRow->getChannel()->getCode() . '_' . $id;

                    $itemArray['order']['items'][] = [
                        'externalId' => $id,
                        'name' => $item->getProduct()->getName(),
                        'sku' => $item->getVariant()->getCode(),
                        'brand' => 'Tediber',
                        'category' => null === $item->getProduct()->getMainTaxon() ? $item->getProduct()->getCode() : $item->getProduct()->getMainTaxon()->getCode(),
                        'variantTitle' => $item->getVariant()->getName(),
                        'imageURL' => 'https://res.cloudinary.com/dgodjz1pn/image/upload/c_scale,dpr_auto,f_auto,q_auto,w_auto:120:400/v1/products/' . $item->getProduct()->getCode() . '/cart/' . $item->getProduct()->getCode(),
                        'currency' => 'EUR',
                        'price' => $priceHT,
                        'quantity' => $item->getQuantity(),
                    ];
                }
            }
        }

        $props = $this->getOrderPromotions($order);

        if (!empty($props)) {
            $itemArray['order']['props'] = $props;
        }

        return $itemArray;
    }

    private function constructSessionBody(OrderStore $orderStore): array
    {
        return [
            'id' => 'session_' . $orderStore->getId(),
            'kind' => 'session',
            'session' => [
                'externalId' => 'session_' . $orderStore->getId(),
                'createdAt' => $orderStore->getOrder()->getCreatedAt()->format('c'),
                'externalUserId' => (string) $orderStore->getOrder()->getCustomer()->getId(),
                'userIsAuthenticated' => true,
                'domainId' => $this->getDomain($orderStore->getOrder(), true),
                'source' => 'tediber.com',
                'medium' => 'devis-pos',
                'timezoneOffset' => $orderStore->getOrder()->getCreatedAt()->getOffset(),
            ],
        ];
    }

    private function constructEventBody(OrderStore $orderStore): array
    {
        $itemArray = [
            'id' => 'event_' . $orderStore->getId(),
            'kind' => 'event',
            'event' => [
                'externalId' => 'event_' . $orderStore->getId(),
                'createdAt' => $orderStore->getOrder()->getCreatedAt()->format('c'),
                'externalUserId' => (string) $orderStore->getOrder()->getCustomer()->getId(),
                'userIsAuthenticated' => true,
                'domainId' => $this->getDomain($orderStore->getOrder(), true),
                'label' => 'devis-pos',
                'value' => $orderStore->getOrder()->getTotal(),
                'externalSessionId' => 'session_' . $orderStore->getId(),
                'nonInteractive' => true,
                'timezoneOffset' => $orderStore->getOrder()->getCreatedAt()->getOffset(),
            ],
        ];

        if ($orderStore->getOrder() && null !== $orderStore->getOrder()->getNumber()) {
            $itemArray['props'] = [
                'order_number' => $orderStore->getOrder()->getNumber(),
            ];
        }

        return $itemArray;
    }

    private function getPaymentStatus(array $orders): string
    {
        $payments = [];

        foreach ($orders as $childArrays) {
            /** @var Order $order */
            foreach ($childArrays as $order) {
                if (!in_array($order->getPaymentState(), $payments)) {
                    $payments[] = $order->getPaymentState();
                }
            }
        }

        if (in_array(OrderPaymentStates::STATE_REFUNDED, $payments)) {
            return self::STATUS_PAYMENT_REFUNDED;
        }

        if (in_array(OrderPaymentStates::STATE_PAID, $payments)) {
            return self::STATUS_PAYMENT_PAID;
        }

        if (in_array(OrderPaymentStates::STATE_PARTIALLY_PAID, $payments)) {
            return self::STATUS_PAYMENT_PARTIALLY_PAID;
        }

        if (in_array(OrderPaymentStates::STATE_AUTHORIZED, $payments)) {
            return self::STATUS_PAYMENT_AUTHORIZED;
        }

        return self::STATUS_PAYMENT_PENDING;
    }

    private function getOrderStatus(array $orders): string
    {
        foreach ($orders as $childArrays) {
            /** @var Order $order */
            foreach ($childArrays as $order) {
                if ('return' !== $order->getChannel()->getCode() && OrderInterfaceAlias::STATE_FULFILLED === $order->getState()) {
                    return self::STATUS_FULFILLED;
                }
            }
        }

        return self::STATUS_PARTIAL;
    }

    private function getOrderRefunds(array $orders): array
    {
        $refunds = [];
        foreach ($orders as $childArrays) {
            /** @var Order $order */
            foreach ($childArrays as $order) {
                if ('return' === $order->getChannel()->getCode() && !$order->getPayments()->isEmpty()) {
                    $itemArray = [];

                    $refundTotal = 0;
                    foreach ($order->getPayments() as $payment) {
                        if (OrderPaymentStates::STATE_REFUNDED === $payment->getState()) {
                            $refundTotal += $payment->getAmount();
                        }
                    }

                    if (null === $order->getShippingAddress() || 'CH' !== $order->getShippingAddress()->getCountryCode()) {
                        $refundTotalHT = ceil($refundTotal / self::BASE_TVA);
                    } else {
                        $refundTotalHT = $refundTotal;
                    }

                    $first = true;
                    /** @var OrderItem $item */
                    foreach ($order->getItems() as $item) {
                        $itemArray[] = [
                            'externalId' => (string) $item->getId(),
                            'quantity' => $item->getQuantity(),
                            'subtotal' => $first ? $refundTotalHT : 0,
                            'totalTax' => $refundTotal - $refundTotalHT,
                            'sku' => $item->getVariant()->getCode(),
                        ];
                        $first = false;
                    }

                    $note = !$order->getOrderReason()->isEmpty() && null !== $order->getOrderReason()->first()->getReason() ? $order->getOrderReason()->first()->getReason()->getName() : '';

                    $refunds[] = [
                        'externalId' => $order->getNumber(),
                        'createdAt' => $order->getCreatedAt()->format('c'),
                        'updatedAt' => $order->getUpdatedAt()->format('c'),
                        'note' => $note,
                        'items' => $itemArray,
                    ];
                }
            }
        }

        return $refunds;
    }

    private function getOrderPromotions(Order $order): array
    {
        $promotions = [];
        foreach ($order->getPromotions() as $promotion) {
            $promotions['promotion_' . $promotion->getCode()] = 'true';
        }

        return $promotions;
    }

    /**
     * @param bool $quotation
     *
     * @todo : switch all of that to constants
     */
    private function getDomain(Order $order, $quotation = false): string
    {
        $channelCode = $order->getChannel()->getCode();

        if ($quotation && null !== $order->getOrderStore() && null !== $order->getOrderStore()->getStore()) {
            $channelCode = 'SAV' === $order->getOrderStore()->getStore()->getName() ? 'sav' : 'boutique';
        }

        switch ($channelCode) {
            case 'boutique':
                $hasStore = null !== $order->getOrderStore() && null !== $order->getOrderStore()->getStore();

                return $hasStore ? strtolower((string) $order->getOrderStore()->getStore()->getName()) : 'ste_croix';

            case 'carrefour':
                $hasStore = null !== $order->getOrderStore() && null !== $order->getOrderStore()->getStore();

                return $hasStore ? str_replace('é', 'e', str_replace(' ', '-', strtolower((string) $order->getOrderStore()->getStore()->getName()))) : 'carrefour-montesson';

            case 'b2b':
            case 'family-friend':
            case 'printemps':
            case 'sav':
                return $channelCode;

            default:
                return 'www-tediber-com';
        }
    }

    private function getRevenue(array $orders): int
    {
        /** @var Order $original */
        $original = current($orders['']);
        $total = $original->getTotal();

        if ($original->getShippingAddress() && 'CH' === $original->getShippingAddress()->getCountryCode()) {
            $totalHT = $total;
        } else {
            $totalHT = $original->getTaxTotal() > 0 ? $total - $original->getTaxTotal() : $total / self::BASE_TVA;
        }

        $refundTotal = 0;
        foreach ($orders as $childArrays) {
            /** @var Order $order */
            foreach ($childArrays as $order) {
                if ('return' === $order->getChannel()->getCode()) {
                    if (!$order->getPayments()->isEmpty()) {
                        foreach ($order->getPayments() as $payment) {
                            $refundTotal += $payment->getAmount();
                        }
                    }
                }
            }
        }

        $totalAfterRefund = $total - $refundTotal;

        return $total > 0 ? $totalAfterRefund * $totalHT / $total : 0;
    }
}
