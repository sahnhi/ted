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

class ChooseShippingActionTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public static function setUpBeforeClass(): void
    {
        self::$dbPopulated = true;
        self::$purgeWithTruncate = false;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testChooseRelayPoint(): void
    {
        self::createClient()->request(Request::METHOD_PUT, '/shop-api/default/fr_FR/order/5811cf75-84a3-4673-9cc3-69755d1b72ad/shipping', [
            'json' => [
                'method' => 'DPWR',
                'relay_id' => 'P93420',
                'relay_address' => [
                    'city' => 'VIENNE',
                    'company' => 'LA SUPERETTE DU JEU DE PAUME',
                    'countryCode' => 'FR',
                    'details' => '',
                    'firstName' => 'LA SUPERETTE DU JEU DE PAUME',
                    'lastName' => 'LA SUPERETTE DU JEU DE PAUME',
                    'postcode' => '38200',
                    'street' => '9 PLACE DU JEU DE PAUME',
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@id' => '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72ad',
            'shippingAddress' => [
                'city' => 'VIENNE',
                'company' => 'LA SUPERETTE DU JEU DE PAUME',
                'countryCode' => 'FR',
                'details' => '',
                'firstName' => 'LA SUPERETTE DU JEU DE PAUME',
                'lastName' => '',
                'postcode' => '38200',
                'street' => '9 PLACE DU JEU DE PAUME',
            ],
            'shipments' => [
                [
                    'method' => [
                        '@id' => '/api/v2/shop/shipping-methods/DPWR',
                    ],
                ],
            ],
        ]);
    }
}
