<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Cart;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Tests\Functional\WebTestCase;

class AddCouponActionTest extends WebTestCase
{
    public function testAddCouponToCart(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';
        $orderRepository = $this->getContainer()->get(OrderRepository::class);

        /** @var Order $cart */
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);

        self::assertNull($cart->getPromotionCoupon());

        $body = [
            'coupon' => 'promotion_1',
        ];
        $this->put('/shop-api/default/fr_FR/carts/' . $token . '/coupon', $body);

        /** @var Order $cart */
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);

        $this->assertNotEmpty($promotion = $cart->getPromotionCoupon());
        $this->assertEquals('promotion_1', $promotion->getCode());
        $this->loadFixture();
    }

    public function testAddCouponToCompletedCart(): void
    {
        $token = '042f6288-1e61-48f9-aedc-3452463d0732';
        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $orderRepository->findOneBy(['tokenValue' => $token]);
        $body = [
            'coupon' => 'promotion_1',
        ];
        $response = $this->put('/shop-api/default/fr_FR/carts/' . $token . '/coupon', $body);
        $this->assertEquals('{"code":400,"message":"Le coupon ne peut \u00eatre ajout\u00e9 \u00e0 votre panier. Merci de supprimer les articles de votre panier et recommencer votre commande"}', $response->getResponseContent());
    }

    public function testAddInvalidCouponToCart(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';
        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);
        $this->assertEmpty($cart->getPromotionCoupon());

        $body = [
            'coupon' => 'promotion_invalid',
        ];

        $response = $this->put('/shop-api/default/fr_FR/carts/' . $token . '/coupon', $body);
        $this->assertEquals('{"code":400,"message":"Le code promo promotion_invalid est invalide."}', $response->getResponseContent());
    }

    public function testAddNoExistingCouponToCart(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';
        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);
        $this->assertEmpty($cart->getPromotionCoupon());
        $body = [
            'coupon' => 'promotion_no_existing',
        ];

        $response = $this->put('/shop-api/default/fr_FR/carts/' . $token . '/coupon', $body);
        $this->assertEquals('{"code":400,"message":"Le code promo promotion_no_existing n\'existe pas."}', $response->getResponseContent());
    }
}
