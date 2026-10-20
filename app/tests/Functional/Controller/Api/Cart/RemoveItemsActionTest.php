<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Cart;

use App\Doctrine\Repository\Sylius\OrderItemRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Tests\Functional\WebTestCase;

class RemoveItemsActionTest extends WebTestCase
{
    public function testRemoveItem(): void
    {
        // we should add firstly item
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';

        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $orderItemRepository = $this->getContainer()->get(OrderItemRepository::class);
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);
        $body = [
            [
                'productCode' => 'mattress',
                'variantCode' => 'TED140190',
                'quantity' => 1,
            ],
        ];
        $this->post('/shop-api/default/fr_FR/carts/' . $token . '/items', $body);
        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);

        $this->assertEquals(1, count($items));

        // 2 delete item
        $itemId = $items[0]->getId();
        $this->delete('/shop-api/default/fr_FR/carts/' . $token . '/items/' . $itemId);

        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);
        $this->assertEquals(0, count($items));

        $this->loadFixture();
    }

    public function testRemoveNoExistingItem(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';
        $response = $this->delete('/shop-api/default/fr_FR/carts/' . $token . '/items/' . 1);

        $this->assertEquals('{"code":400,"message":"Cart item with 1 id does not exists"}', $response->getResponseContent());
        $this->assertEquals(400, $response->getStatusCode());
    }
}
