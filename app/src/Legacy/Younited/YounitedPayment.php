<?php

namespace App\Legacy\Younited;

use Sylius\Component\Core\Model\OrderInterface;

class YounitedPayment implements YounitedPaymentInterface
{
    public function __construct(
        private readonly YounitedApiInterface $younitedApi,
        private readonly string $environment,
        private readonly string $callbackUrl,
        private readonly OrderInterface $order
    ) {
    }

    public function authorize(): array
    {
        $this->younitedApi->setApiUrl($this->younitedApi->getLoginUrl());

        $this->younitedApi->setCredentials([
            'grant_type' => $this->younitedApi->getGrantType(),
            'client_id' => $this->younitedApi->getClientId(),
            'client_secret' => $this->younitedApi->getClientSecret(),
            'resource' => $this->younitedApi->getResource(),
        ]);

        return ['bearer' => $this->younitedApi->executeRequest()];
    }

    public function sessionInitialize(string $authorizeToken): array
    {
        $this->resolveEnvironment();

        $this->younitedApi->setAuthorization($authorizeToken);

        $this->younitedApi->setResponse([
            'callbackUrl' => $this->callbackUrl,
            'firstName' => $this->order->getShippingAddress()->getFirstName(),
            'lastName' => $this->order->getShippingAddress()->getLastName(),
            'mobilePhoneNumber' => $this->order->getShippingAddress()->getPhoneNumber(),
            'emailAddress' => $this->order->getCustomer()->getEmail(),
            'deliveryAddress' => [
                'street' => $this->order->getShippingAddress()->getStreet(),
                'additionnalInfo' => '',
                'postalCode' => $this->order->getShippingAddress()->getPostcode(),
                'city' => $this->order->getShippingAddress()->getCity(),
                'countryCode' => $this->order->getShippingAddress()->getCountryCode(),
            ],
            'basket' => $this->basketItems($this->order),
            'basketAmount' => $this->order->getTotal() / 100,
            'orderHashReference' => $this->order->getTokenValue(),
        ]);

        return $this->younitedApi->executeRequest('/api/session/initialize');
    }

    public function contractActivate(string $authorizeToken): array
    {
        $this->resolveEnvironment();

        $this->younitedApi->setAuthorization($authorizeToken);

        $this->younitedApi->setResponse([
            'orderHashReference' => $this->order->getTokenValue(),
        ]);

        return $this->younitedApi->executeRequest('/api/contract/activate');
    }

    public function contractCancel(string $authorizeToken): array
    {
        $this->resolveEnvironment();

        $this->younitedApi->setAuthorization($authorizeToken);

        $this->younitedApi->setResponse([
            'orderHashReference' => $authorizeToken,
        ]);

        return $this->younitedApi->executeRequest('/api/contract/cancel');
    }

    public function contractRestitute(string $authorizeToken): array
    {
        $this->resolveEnvironment();

        $this->younitedApi->setAuthorization($authorizeToken);

        $this->younitedApi->setResponse([
            'orderHashReference' => $authorizeToken,
        ]);

        return $this->younitedApi->executeRequest('/api/contract/restitute');
    }

    private function basketItems(OrderInterface $order): ?array
    {
        $items = [];

        foreach ($order->getItems() as $cartItem) {
            $items[] = [
                'productName' => $cartItem->getProductName(),
                'quantity' => $cartItem->getQuantity(),
                'unitPrice' => $cartItem->getUnitPrice() / 100,
            ];
        }

        return $items;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function resolveEnvironment(): void
    {
        if (YounitedApiInterface::DEVELOPMENT === $this->environment) {
            $this->younitedApi->setApiUrl(YounitedApiInterface::DEVELOPMENT);

            return;
        }

        if (YounitedApiInterface::PRODUCTION === $this->environment) {
            $this->younitedApi->setApiUrl(YounitedApiInterface::PRODUCTION);

            return;
        }

        throw new \InvalidArgumentException(sprintf('The %s environment is invalid. Expected %s or %s', $this->environment, YounitedApi::DEVELOPMENT, YounitedApi::PRODUCTION));
    }
}
