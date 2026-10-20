<?php

namespace Sokeo\SyliusYounitedpayPlugin\Api;

use Payum\Core\Request\GetHttpRequest;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class YounitedpayContract implements YounitedpayContractInterface
{
    /** @var YounitedpayApi */
    private $younitedpayApi;

    public function __construct(
        YounitedpayApi $younitedpayApi,
        private readonly Logger $logger
    ) {
        $this->younitedpayApi = $younitedpayApi;
    }

    public function initializeContract(
        OrderInterface $order,
        PaymentInterface $payment,
        int $maturity,
        UrlGeneratorInterface $router,
        GetHttpRequest $request
    ): array {
        $redirectUrl = !empty($request->query['redirectUrl']) ?
            $request->query['redirectUrl'] . '&status=completed' :
            str_replace('http:', 'https:', $router->generate('sylius_shop_order_thank_you', [], UrlGeneratorInterface::ABSOLUTE_URL));
        $cancelUrl = !empty($request->query['cancelUrl']) ?
            $request->query['cancelUrl'] . '?younited=failed' :
            str_replace('http:', 'https:', $router->generate('sylius_shop_checkout_complete', [], UrlGeneratorInterface::ABSOLUTE_URL));

        $options = [
            'json' => [
                'requestedMaturity' => $maturity,
                'personalInformation' => [
                    'firstName' => $order->getShippingAddress()->getFirstName(),
                    'lastName' => $order->getShippingAddress()->getLastName(),
                    'genderCode' => null, // MALE or FEMALE
                    'emailAddress' => $order->getCustomer()->getEmail(),
                    'cellPhoneNumber' => $this->cellPhoneNumber($order), // +33 format
                    'birthDate' => null, // 1987-08-24T14:15:22Z format
                    'address' => [
                        'streetNumber' => '',
                        'streetName' => substr($order->getShippingAddress()->getStreet(), 0, 38),
                        'additionalAddress' => (substr($order->getShippingAddress()->getStreet(), 38) ? substr($order->getShippingAddress()->getStreet(), 38) : ''),
                        'city' => $order->getShippingAddress()->getCity(),
                        'postalCode' => $order->getShippingAddress()->getPostcode(),
                        'countryCode' => $order->getShippingAddress()->getCountryCode(),
                    ],
                ],
                'basket' => $this->basketItems($order),
                'merchantUrls' => [
                    'onGrantedWebhookUrl' => str_replace('http:', 'https:', $router->generate('sylius_younitedpay_plugin_webhook_granted', ['paymentId' => $payment->getId()], UrlGeneratorInterface::ABSOLUTE_URL)),
                    'onCanceledWebhookUrl' => str_replace('http:', 'https:', $router->generate('sylius_younitedpay_plugin_webhook_cancelled', ['paymentId' => $payment->getId()], UrlGeneratorInterface::ABSOLUTE_URL)),
                    'onWithdrawnWebhookUrl' => str_replace('http:', 'https:', $router->generate('sylius_younitedpay_plugin_webhook_withdrawn', ['paymentId' => $payment->getId()], UrlGeneratorInterface::ABSOLUTE_URL)),
                    'onApplicationSucceededRedirectUrl' => $redirectUrl,
                    'onApplicationFailedRedirectUrl' => $cancelUrl,
                ],
                'merchantOrderContext' => [
                    'channel' => 'ONLINE', // ONLINE or PHYSICAL
                    'shopCode' => 'ONLINE',
                    'agentEmailAddress' => 'it@tediber.com',
                    'merchantReference' => $order->getNumber(),
                ],
            ],
        ];

        $this->logger->info('[YOUNITED] captureAction', [
            'uri' => $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            'payload' => $options,
        ]);

        return $this->younitedpayApi->executeRequest(
            $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            self::INITIALIZE_CONTRACT_METHOD,
            self::INITIALIZE_CONTRACT_URI,
            $options
        );
    }

    public function confirmContract(string $contractReference): array
    {
        return $this->younitedpayApi->executeRequest(
            $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            self::CONFIRM_CONTRACT_METHOD,
            $this->replaceContractReferenceInUri(self::CONFIRM_CONTRACT_URI, $contractReference),
            [
                'json' => [
                    'merchantOrderId' => '',
                ],
            ]
        );
    }

    public function activateContract(string $contractReference): array
    {
        return $this->younitedpayApi->executeRequest(
            $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            self::ACTIVATE_CONTRACT_METHOD,
            $this->replaceContractReferenceInUri(self::ACTIVATE_CONTRACT_URI, $contractReference)
        );
    }

    public function withdrawContract(string $contractReference, $amount = 0): array
    {
        return $this->younitedpayApi->executeRequest(
            $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            self::WITHDRAW_CONTRACT_METHOD,
            $this->replaceContractReferenceInUri(self::WITHDRAW_CONTRACT_URI, $contractReference),
            [
                'json' => [
                    'amount' => ((0 == $amount) ? '' : $amount),
                ],
            ]
        );
    }

    public function cancelContract(string $contractReference): array
    {
        return $this->younitedpayApi->executeRequest(
            $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            self::CANCEL_CONTRACT_METHOD,
            $this->replaceContractReferenceInUri(self::CANCEL_CONTRACT_URI, $contractReference)
        );
    }

    public function loadContract(string $contractReference): array
    {
        return $this->younitedpayApi->executeRequest(
            $this->younitedpayApi->getYounitedpayApiBaseUrl(),
            self::LOAD_CONTRACT_METHOD,
            $this->replaceContractReferenceInUri(self::LOAD_CONTRACT_URI, $contractReference)
        );
    }

    public function getLoadLabel(): string
    {
        return self::LOAD_LABEL;
    }

    public function getCancelLabel(): string
    {
        return self::CANCEL_LABEL;
    }

    public function getConfirmLabel(): string
    {
        return self::CONFIRM_LABEL;
    }

    public function getActivateLabel(): string
    {
        return self::ACTIVATE_LABEL;
    }

    public function getWithdrawLabel(): string
    {
        return self::WITHDRAW_LABEL;
    }

    public function getInitializeLabel(): string
    {
        return self::INITIALIZE_LABEL;
    }

    private function basketItems(OrderInterface $order): ?array
    {
        $items = [
            'basketAmount' => $order->getTotal() / 100,
            'items' => [],
        ];

        foreach ($order->getItems() as $cartItem) {
            if ($cartItem->getUnitPrice() > 0) {
                $items['items'][] = [
                    'itemName' => $cartItem->getProductName(),
                    'quantity' => $cartItem->getQuantity(),
                    'unitPrice' => $cartItem->getUnitPrice() / 100,
                ];
            }
        }

        return $items;
    }

    private function cellPhoneNumber(OrderInterface $order)
    {
        $phone = $order->getShippingAddress()->getPhoneNumber();
        // Correct format for phone number => +33xxxxxxxxx
        $phone = str_replace('', '', $phone);
        if (preg_match('/^0[1-9]\d{8}$/', $phone)) {
            $phone = '+33' . substr($phone, 1);
        } elseif (preg_match('/^0033[1-9]\d{8}$/', $phone)) {
            $phone = '+' . substr($phone, 2);
        } elseif (!preg_match('/^\+33[1-9]\d{8}$/', $phone)) {
            return false;
        }

        return $phone;
    }

    private function replaceContractReferenceInUri($fullString, $contractReference, $stringToReplace = self::CONTRACT_REFERENCE_STRING_TO_REPLACE)
    {
        return str_replace($stringToReplace, $contractReference, $fullString);
    }
}
