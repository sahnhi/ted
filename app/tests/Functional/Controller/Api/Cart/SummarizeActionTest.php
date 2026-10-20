<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Cart;

use App\Tests\Functional\WebTestCase;

class SummarizeActionTest extends WebTestCase
{
    public function testGetCart(): void
    {
        // we should add firstly item
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';

        $body = [
            [
                'productCode' => 'mattress',
                'variantCode' => 'TED140190',
                'quantity' => 1,
            ],
        ];
        $this->post('/shop-api/default/fr_FR/carts/' . $token . '/items', $body);

        // 2 get cart
        $response = $this->get('/shop-api/default/fr_FR/carts/' . $token);
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../fixtures/api.cart.summarize.existing.json', $response->getResponseContent());
        $this->loadFixture();
    }

    public function testGetCartWithPromotion(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';

        $this->post(sprintf('/shop-api/default/fr_FR/carts/%s/items', $token), [
            [
                'productCode' => 'mattress',
                'variantCode' => 'TED140190',
                'quantity' => 1,
            ],
        ]);

        $this->put(sprintf('/shop-api/default/fr_FR/carts/%s/coupon', $token), [
            'coupon' => 'promotion_15',
        ]);

        $response = $this->get(sprintf('/shop-api/default/fr_FR/carts/%s', $token));

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../fixtures/api.cart.summarize.existing_with_promotion.json', $response->getResponseContent());
        $this->loadFixture();
    }

    public function testGetNotExistingCart(): void
    {
        $token = 'e2d82574-c887-4eb8-9144-93a937adbde5';
        $response = $this->get('/shop-api/default/fr_FR/carts/' . $token);
        $this->assertEquals(404, $response->getStatusCode());
        $expectedResponse = '{"code":404,"message":"Cart with given id does not exists"}';
        $this->assertEquals($expectedResponse, $response->getResponseContent());
    }
}
