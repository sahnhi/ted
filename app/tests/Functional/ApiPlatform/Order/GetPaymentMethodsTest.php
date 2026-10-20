<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetPaymentMethodsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private HttpClientInterface $client;

    public static function setUpBeforeClass(): void
    {
        self::$dbPopulated = true;
        self::$purgeWithTruncate = false;
    }

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->disableReboot();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testItReturnsTheAvailablePaymentMethods(): void
    {
        $response = $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount', // Price: 50000
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'payments' => [
                [
                    '@type' => 'Payment',
                    'method' => [
                        '@id' => '/api/v2/shop/payment-methods/adyen_cb',
                        '@type' => 'PaymentMethod',
                    ],
                ],
            ],
            'itemsTotal' => 50000,
            'total' => 51000, // Lower than the 60000 configured for 'adyen_cb'
            'orderPromotionTotal' => 0,
        ]);

        $paymentId = $response->toArray()['payments'][0]['id'];

        $this->client->request(Request::METHOD_GET, sprintf('/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/payments/%d/methods', $paymentId));

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/PaymentMethod',
            '@id' => sprintf('/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/payments/%d/methods', $paymentId),
            '@type' => 'hydra:Collection',
            'hydra:member' => [
                [
                    '@id' => '/api/v2/shop/payment-methods/adyen_cb',
                    '@type' => 'PaymentMethod',
                    'rules' => [
                        [
                            '@type' => 'PaymentMethodRule',
                            'type' => '<',
                            'amount' => 60000,
                            'paymentMethod' => '/api/v2/shop/payment-methods/adyen_cb',
                        ],
                    ],
                    'channels' => [
                        0 => '/api/v2/shop/channels/default',
                        1 => '/api/v2/shop/channels/boutique',
                    ],
                    'gatewayConfig' => [
                        '@type' => 'GatewayConfig',
                        'factoryName' => 'adyen',
                        'gatewayName' => 'adyen_cb',
                        'config' => [],
                        '__initializer__' => null,
                        '__cloner__' => null,
                        '__isInitialized__' => true,
                    ],
                    'code' => 'adyen_cb',
                    'name' => 'Adyen',
                    'description' => 'Adyen Description',
                    'instructions' => 'Adyen Instructions',
                    'environment' => null,
                    'position' => 1,
                    'translation' => [
                        '@type' => 'PaymentMethodTranslation',
                        'name' => 'Adyen',
                        'description' => 'Adyen Description',
                        'instructions' => 'Adyen Instructions',
                        'translatable' => '/api/v2/shop/payment-methods/adyen_cb',
                        'locale' => 'fr_FR',
                    ],
                    'enabled' => true,
                    'translations' => [
                        'fr_FR' => [
                            '@type' => 'PaymentMethodTranslation',
                            'name' => 'Adyen',
                            'description' => 'Adyen Description',
                            'instructions' => 'Adyen Instructions',
                            'translatable' => '/api/v2/shop/payment-methods/adyen_cb',
                            'locale' => 'fr_FR',
                        ],
                    ],
                ],
            ],
            'hydra:totalItems' => 1,
        ]);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testItDoesntReturnsThePaymentMethodIfTheRulesDoNotMatch(): void
    {
        $response = $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount', // Price: 50000
                'quantity' => 2, // Total : 100000, which is greater than configured 60000 for adyen_cb
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'payments' => [
                [
                    '@type' => 'Payment',
                    'method' => [
                        '@id' => '/api/v2/shop/payment-methods/adyen_cb',
                        '@type' => 'PaymentMethod',
                    ],
                ],
            ],
            'itemsTotal' => 100000,
            'total' => 101000, // Greater than the 60000 configured for 'adyen_cb'
            'orderPromotionTotal' => 0,
        ]);

        $paymentId = $response->toArray()['payments'][0]['id'];

        $this->client->request(Request::METHOD_GET, sprintf('/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/payments/%d/methods', $paymentId));

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/PaymentMethod',
            '@id' => sprintf('/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/payments/%d/methods', $paymentId),
            '@type' => 'hydra:Collection',
            'hydra:member' => [],
            'hydra:totalItems' => 0,
        ]);
    }
}
