<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Cart;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Tests\Functional\WebTestCase;

class DropActionTest extends WebTestCase
{
    public function testDropCart(): void
    {
        $token = '5811cf75-84a3-4673-9cc3-00755d1b72ad';

        $orderRepository = $this->getContainer()->get(OrderRepository::class);
        $this->assertNotNull($orderRepository->findOneBy(['tokenValue' => $token]));
        $this->delete('/shop-api/default/fr_FR/carts/' . $token);
        $this->assertNull($orderRepository->findOneBy(['tokenValue' => $token]));

        $this->loadFixture();
    }

    public function testDropNoExistingCart(): void
    {
        $noExistingToken = '1111cf87-84a3-4673-9cc3-00755d1b72e4';
        $response = $this->delete('/shop-api/default/fr_FR/carts/' . $noExistingToken);

        $this->assertEquals('{"code":500,"message":"Order with 1111cf87-84a3-4673-9cc3-00755d1b72e4 token has not been found."}', $response->getResponseContent());
        $this->assertEquals(500, $response->getStatusCode());
    }
}
