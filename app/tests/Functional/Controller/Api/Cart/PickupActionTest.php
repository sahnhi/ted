<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Cart;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Tests\Functional\WebTestCase;
use Ramsey\Uuid\Uuid;

class PickupActionTest extends WebTestCase
{
    public function testCreateCart(): void
    {
        $response = $this->post('/shop-api/default/fr_FR/carts');
        $responseContent = json_decode($response->getResponseContent(), true);

        $token = $responseContent['tokenValue'];
        $this->assertTrue(Uuid::isValid($responseContent['tokenValue']));
        $expectedResponse = '{"channel":"default","currency":"EUR","locale":"fr_FR","checkoutState":"cart","items":[],"totals":{"undiscountedPrice":0,"total":0,"items":0,"taxes":0,"taxesRate":0.2,"shipping":0,"promotion":0,"ecotax":0,"amountGiftCardUsed":0,"amountGiftCardLeft":0},"payments":[],"shipments":[],"cartDiscounts":[], "relatedProducts":[], "gamification":[]}';

        unset($responseContent['tokenValue']);
        $this->assertEquals(json_decode($expectedResponse, true), $responseContent);

        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $this->assertNotNull($orderRepository->findOneBy(['tokenValue' => $token]));
        $this->loadFixture();
    }
}
