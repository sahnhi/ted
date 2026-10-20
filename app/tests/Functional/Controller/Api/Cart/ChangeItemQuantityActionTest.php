<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Cart;

use App\Doctrine\Repository\Sylius\OrderItemRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Tests\Functional\WebTestCase;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;

class ChangeItemQuantityActionTest extends WebTestCase
{
    public function testChangeItemQuantity(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';

        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $orderItemRepository = $this->getContainer()->get(OrderItemRepository::class);
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);

        // 1 add item
        $body = [
            [
                'productCode' => 'mattress',
                'variantCode' => 'TED140190',
                'quantity' => 1,
            ],
        ];
        $this->post('/shop-api/default/fr_FR/carts/' . $token . '/items', $body);

        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);
        $itemId = $items[0]->getId();

        // update Quantity
        $body = [
            'quantity' => 2,
        ];
        $this->put('/shop-api/default/fr_FR/carts/' . $token . '/items/' . $itemId, $body);

        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);
        $item = $items[0];

        $this->assertEquals(2, $item->getQuantity());
        $this->loadFixture();
    }

    public function testChangeItemQuantityToZero(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';

        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $orderItemRepository = $this->getContainer()->get(OrderItemRepository::class);
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);

        // 1 add item
        $body = [
            [
                'productCode' => 'mattress',
                'variantCode' => 'TED140190',
                'quantity' => 1,
            ],
        ];
        $this->post('/shop-api/default/fr_FR/carts/' . $token . '/items', $body);

        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);
        $itemId = $items[0]->getId();

        // update Quantity
        $body = [
            'quantity' => 0,
        ];
        $response = $this->put('/shop-api/default/fr_FR/carts/' . $token . '/items/' . $itemId, $body);

        $this->assertEquals('{"code":500,"message":"Quantity should be greater than 0"}', $response->getResponseContent());
    }

    public function createPaymentTokenStorage(\Payum\Core\Storage\StorageInterface $tokenStorage): void
    {
        /** @var PaymentSecurityTokenInterface $paymentToken */
        $paymentToken = $tokenStorage->create();
        $paymentToken->setHash('F37ZwbrTU1-wzG8Pr09Ubn4JEq8Iyu6b57mpn-BuKgM');
        $paymentToken->setTargetUrl('osef');
        $paymentToken->setGatewayName('osef');
        $paymentToken->setDetails('C:25:"Payum\Core\Model\Identity":66:{a:2:{i:0;i:601726;i:1;s:35:"Sylius\Component\Core\Model\Payment";}}');
        $tokenStorage->update($paymentToken);
    }
}
