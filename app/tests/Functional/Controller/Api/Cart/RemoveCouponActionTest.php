<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Cart;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Tests\Functional\WebTestCase;

class RemoveCouponActionTest extends WebTestCase
{
    public function testDeleteCoupon(): void
    {
        $token = '042f6288-1e61-48f9-aedc-3452463d0731';
        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);

        $this->assertNotEmpty($coupon = $cart->getPromotionCoupon());

        $this->delete('/shop-api/default/fr_FR/carts/' . $token . '/coupon');
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);
        $this->assertEmpty($cart->getPromotionCoupon());
        $this->loadFixture();
    }

    public function testDeleteCouponThrowExceptionOnCompletedOrder(): void
    {
        $token = '042f6288-1e61-48f9-aedc-3452463d0732';
        $response = $this->delete('/shop-api/default/fr_FR/carts/' . $token . '/coupon');
        $this->assertEquals('{"code":400,"message":"Le coupon ne peut \u00eatre retir\u00e9 de votre panier. Merci de supprimer les articles de votre panier et recommencer votre commande"}', $response->getResponseContent());
    }
}
