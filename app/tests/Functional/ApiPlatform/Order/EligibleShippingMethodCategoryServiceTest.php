<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EligibleShippingMethodCategoryServiceTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public static function setUpBeforeClass(): void
    {
        self::$dbPopulated = true;
        self::$purgeWithTruncate = false;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testTheQuantityOfARestrictedServiceAlwaysMatchesTheQuantityOfTheRestrictedProduct(): void
    {
        $client = self::createClient();
        $client->disableReboot();

        [$tokenValue, $mattressOrderItemId] = $this->setupOrder($client);

        // Even though we select four 'ECO_ASSEMBLY_RESTRICTED' we only can have three, since it is
        // restricted the mattress (which we have three in the cart)
        $client->request(Request::METHOD_PATCH, sprintf('/api/v2/shop/orders/%s/shipping-method-category', $tokenValue), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'shippingMethodCategoryCode' => 'ECONOMIQUE',
                'serviceCodes' => [
                    'ECO_ASSEMBLY_RESTRICTED',
                    'ECO_ASSEMBLY_RESTRICTED',
                    'ECO_ASSEMBLY_RESTRICTED',
                    'ECO_ASSEMBLY_RESTRICTED',
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [
                [
                    'variant' => [
                        'code' => 'TED160200',
                    ],
                    'quantity' => 3,
                ],
                [
                    'variant' => [
                        'code' => 'recuperation_variant',
                    ],
                    'quantity' => 1,
                ],
                [
                    'variant' => [
                        'code' => 'assembly_variant',
                    ],
                    'quantity' => 3,
                ],
            ],
        ]);

        // Now, if we set the quantity to two, we should only have two 'ECO_ASSEMBLY_RESTRICTED'
        $client->request(Request::METHOD_PATCH, sprintf('/api/v2/shop/orders/%s/items/%s', $tokenValue, $mattressOrderItemId), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'quantity' => 2,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [
                [
                    'variant' => [
                        'code' => 'TED160200',
                    ],
                    'quantity' => 2,
                ],
                [
                    'variant' => [
                        'code' => 'recuperation_variant',
                    ],
                    'quantity' => 1,
                ],
                [
                    'variant' => [
                        'code' => 'assembly_variant',
                    ],
                    'quantity' => 2,
                ],
            ],
        ]);

        // Now, if we delete the mattress order item, the two services should also be removed from the cart
        $client->request(Request::METHOD_DELETE, sprintf('/api/v2/shop/orders/%s/items/%s', $tokenValue, $mattressOrderItemId));

        self::assertResponseIsSuccessful();

        $client->request(Request::METHOD_GET, sprintf('/api/v2/shop/orders/%s', $tokenValue));

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [],
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testItRemovesTheAssociatedServiceIfWeChangeTheShippingMethodCategory(): void
    {
        $client = self::createClient();
        $client->disableReboot();

        [$tokenValue, $mattressOrderItemId] = $this->setupOrder($client);

        // Even though we select four 'ECO_ASSEMBLY_RESTRICTED' we only can have three, since it is
        // restricted the mattress (which we have three in the cart)
        $client->request(Request::METHOD_PATCH, sprintf('/api/v2/shop/orders/%s/shipping-method-category', $tokenValue), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'shippingMethodCategoryCode' => 'ECONOMIQUE',
                'serviceCodes' => [
                    'ECO_ASSEMBLY_RESTRICTED',
                    'ECO_ASSEMBLY_RESTRICTED',
                    'ECO_ASSEMBLY_RESTRICTED',
                    'ECO_ASSEMBLY_RESTRICTED',
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [
                [
                    'variant' => [
                        'code' => 'TED160200',
                    ],
                    'quantity' => 3,
                ],
                [
                    'variant' => [
                        'code' => 'recuperation_variant',
                    ],
                    'quantity' => 1,
                ],
                [
                    'variant' => [
                        'code' => 'assembly_variant',
                    ],
                    'quantity' => 3,
                ],
            ],
        ]);

        // We now change the selected shipping method category
        $client->request(Request::METHOD_PATCH, sprintf('/api/v2/shop/orders/%s/shipping-method-category', $tokenValue), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'shippingMethodCategoryCode' => 'COMFORT',
                'serviceCodes' => [],
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [
                [
                    'variant' => [
                        'code' => 'TED160200',
                    ],
                    'quantity' => 3,
                ],
            ],
        ]);
    }

    /**
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function setupOrder(HttpClientInterface $client): array
    {
        $response = $client->request(Request::METHOD_POST, '/api/v2/shop/orders', [
            'json' => [
                'localeCode' => 'fr_FR',
            ],
        ]);

        self::assertResponseIsSuccessful();

        $tokenValue = $response->toArray()['tokenValue'];

        self::assertNotEmpty($tokenValue);

        $addItemsResponse = $client->request(Request::METHOD_PATCH, sprintf('/api/v2/shop/orders/%s/items', $tokenValue), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/TED160200',
                'quantity' => 3,
                'addons' => [],
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [
                [
                    'variant' => [
                        '@id' => '/api/v2/shop/product-variants/TED160200',
                        'code' => 'TED160200',
                    ],
                    'quantity' => 3,
                ],
            ],
        ]);

        $mattressOrderItemId = $addItemsResponse->toArray()['items'][0]['id'];

        self::assertNotEmpty($mattressOrderItemId);

        // The cart needs to have its checkout_state set to 'addressed' so we can select and shipping method category
        $client->request(Request::METHOD_PATCH, sprintf('/api/v2/shop/orders/%s/address', $tokenValue), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'customer' => [
                    'email' => 'not_batman@wayneenterprise.com',
                    'phoneNumber' => '0789786756',
                    'firstName' => 'Bruce',
                    'lastName' => 'Wayne',
                    'newsletter' => false,
                    'optin' => false,
                    'optinPhone' => false,
                ],
                'shippingAddress' => [
                    'firstName' => 'Batman',
                    'lastName' => 'TheRealOne',
                    'city' => 'Gotham',
                    'street' => 'Bat Cave',
                    'countryCode' => 'FR',
                    'postcode' => '38200',
                    'provinceName' => 'I still do not know',
                    'provinceCode' => 'STILL-IDK',
                    'phoneNumber' => '0789786756',
                    'company' => 'Wayne Enterprise',
                    'details' => 'Gift for Alfred <3',
                    'tva' => '',
                ],
                'billingAddress' => [
                    'firstName' => 'Bruce',
                    'lastName' => 'Wayne',
                    'city' => 'Gotham',
                    'street' => 'Gotham Avenue',
                    'countryCode' => 'FR',
                    'postcode' => '38200',
                    'provinceName' => 'No idea',
                    'provinceCode' => 'IDK',
                    'phoneNumber' => '0789786756',
                    'company' => 'Wayne Enterprise',
                    'details' => 'This is TOTALLY NOT Batman\'s address',
                    'tva' => '',
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();

        return [$tokenValue, $mattressOrderItemId];
    }
}
