<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Cart;

use App\Doctrine\Repository\Sylius\OrderItemRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Tests\Functional\WebTestCase;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;

class PutItemsActionTest extends WebTestCase
{
    public function testAddItem(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';

        $orderRepository = self::$container->get(OrderRepository::class);
        $orderItemRepository = self::$container->get(OrderItemRepository::class);
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);
        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);

        self::assertCount(0, $items);

        $body = [
            [
                'productCode' => 'mattress',
                'variantCode' => 'TED140190',
                'quantity' => 1,
            ],
        ];
        $response = $this->post('/shop-api/default/fr_FR/carts/' . $token . '/items', $body);

        $responseContent = json_decode($response->getResponseContent(), true);
        $expectedContent = json_decode(file_get_contents(__DIR__ . '/../../../fixtures/api.cart.put_items.json'), true);

        self::assertEquals($expectedContent, $responseContent);

        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);
        self::assertCount(1, $items);
        $this->loadFixture();
    }

    /**
     * @dataProvider  AddItemErrorDataProvider
     */
    public function testAddItemWhenShouldReturnError(array $body, array $messageError, int $statusCode): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';

        $orderRepository = self::$container->get(OrderRepository::class);
        $orderItemRepository = self::$container->get(OrderItemRepository::class);
        $cart = $orderRepository->findOneBy(['tokenValue' => $token]);
        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);
        $this->assertEmpty($items);
        $response = $this->post('/shop-api/default/fr_FR/carts/' . $token . '/items', $body, ['Content-Type' => 'application/json']);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($messageError, json_decode($response->getResponseContent(), true));

        $items = $orderItemRepository->findBy(['order' => $cart->getId()]);
        $this->assertEmpty($items);
        $this->loadFixture();
    }

    public function AddItemErrorDataProvider(): array
    {
        return
            [
                [
                    [
                        [
                            'productCode' => 'no code',
                            'variantCode' => 'TED140190',
                            'quantity' => 1,
                        ],
                    ],
                    json_decode('{"code":400,"message":"Validation failed","errors":[{"productCode":["sylius.shop_api.product.exists"]}]}', true),
                    400,
                ],
                [
                    [
                        [
                            'productCode' => 'mattress',
                            'variantCode' => 'no code',
                            'quantity' => 1,
                        ],
                    ],
                    json_decode('{"code":500,"message":"Product variant has not been found"}', true),
                    500,
                ],
                [
                    [
                        [
                            'productCode' => 'mattress',
                            'variantCode' => 'TED140190',
                            'quantity' => 0,
                        ],
                    ],
                    json_decode('{"code":400,"message":"Validation failed","errors":[{"quantity":["sylius.shop_api.quantity.greater_than_0"]}]}', true),
                    400,
                ],
        ];
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
